@extends('pages.pasak.layout', ['heading' => __('Live Threat Map'), 'subheading' => __('Visualisasi intelijen serangan siber real-time berbasis telemetri server')])

@section('content')
@php($payloadData = $payload)
@php($s = $stats)
@php($target = $payloadData['target'] ?? ['name' => 'Pekanbaru, Riau (ID)', 'lat' => 0.5071, 'lon' => 101.4478])
@php($enforcement = !empty($payloadData['enforcement']))

{{-- Cyber Threat Map Container (ThreatsEye SOC Style) --}}
<div class="sec-card sec-card-flush threat-map-card" data-threat-map>
    {{-- 1. Top Tactical HUD Status Bar --}}
    <div class="threat-hud-bar">
        <div class="threat-hud-left">
            <div class="threat-live-indicator">
                <span class="threat-live-beacon"></span>
                <span class="threat-live-label">LIVE RADAR STREAMING</span>
            </div>
            <div class="threat-target-badge" title="{{ __('Server Target yang Dilindungi WAF') }}">
                <span class="threat-hud-icon">🛡️</span>
                <span class="threat-hud-text">NODE:</span>
                <strong class="threat-target-name" data-map-target>{{ $target['name'] }}</strong>
                <span class="threat-target-coords">[{{ number_format($target['lat'], 4) }}° N, {{ number_format($target['lon'], 4) }}° E]</span>
            </div>
            <div class="threat-mode-badge {{ $enforcement ? 'is-enforcing' : 'is-monitoring' }}">
                <span class="threat-mode-dot">●</span>
                <span>{{ $enforcement ? __('WAF BLOCKING ACTIVE (HTTP 403)') : __('MONITORING ONLY (PASSIVE LOG)') }}</span>
            </div>
        </div>

        <div class="threat-hud-right">
            <div class="threat-hud-clock" data-threat-clock>
                <span class="threat-clock-time">--:--:--</span>
                <span class="threat-clock-tz">WIB / UTC</span>
            </div>
            <div class="threat-hud-stat is-danger">
                <span class="threat-hud-stat-label">{{ __('Attacks (Today)') }}</span>
                <span class="threat-hud-stat-val" data-map-attacks>{{ number_format($s['logs_today'] ?? 0) }}</span>
            </div>
            <div class="threat-hud-stat is-critical">
                <span class="threat-hud-stat-label">{{ __('Critical') }}</span>
                <span class="threat-hud-stat-val">{{ number_format($s['critical_today'] ?? 0) }}</span>
            </div>
            <div class="threat-hud-stat is-warning">
                <span class="threat-hud-stat-label">{{ __('Quarantined') }}</span>
                <span class="threat-hud-stat-val">{{ number_format($s['active_blocks'] ?? 0) }}</span>
            </div>
            <div class="threat-hud-stat is-cyan">
                <span class="threat-hud-stat-label">{{ __('Unique IPs') }}</span>
                <span class="threat-hud-stat-val">{{ number_format($s['unique_ips_today'] ?? count($payloadData['origins'] ?? [])) }}</span>
            </div>
        </div>
    </div>

    {{-- 2. Main Viewport (Canvas + Tactical Sidebar) --}}
    <div class="threat-map-wrap">
        {{-- Canvas Viewport --}}
        <div class="threat-map-canvas-holder">
            {{-- Background Tactical Grid & Crosshair Lines --}}
            <div class="threat-grid-overlay" aria-hidden="true"></div>

            <canvas data-map-base></canvas>
            <canvas data-map-anim></canvas>

            {{-- Floating Tactical Viewport Controls HUD --}}
            <div class="threat-controls-hud">
                {{-- Projection Mode Switcher: 3D Globe vs 2D Tactical Map --}}
                <div class="threat-ctrl-group">
                    <button type="button" class="threat-ctrl-btn active" data-map-proj="orthographic" title="{{ __('3D Orthographic Globe') }}">
                        <span>🌐 3D Globe</span>
                    </button>
                    <button type="button" class="threat-ctrl-btn" data-map-proj="equirectangular" title="{{ __('2D Tactical Flat Map') }}">
                        <span>🗺️ 2D Flat</span>
                    </button>
                </div>

                {{-- Map Navigation (Zoom / Rotate / Reset) --}}
                <div class="threat-ctrl-group">
                    <button type="button" class="threat-ctrl-btn" data-map-action="zoom-in" title="{{ __('Zoom In (+)') }}">➕</button>
                    <button type="button" class="threat-ctrl-btn" data-map-action="zoom-out" title="{{ __('Zoom Out (-)') }}">➖</button>
                    <button type="button" class="threat-ctrl-btn" data-map-action="reset" title="{{ __('Reset Pandangan ke Node Target') }}">🎯</button>
                    <button type="button" class="threat-ctrl-btn active" data-map-action="toggle-rotate" title="{{ __('Rotasi Otomatis Globe') }}">🔄 <span class="threat-btn-txt">Spin</span></button>
                </div>

                {{-- Sound FX Toggle (Synthetic Cyber Radar Ping) --}}
                <div class="threat-ctrl-group">
                    <button type="button" class="threat-ctrl-btn" data-map-sound title="{{ __('Audio Radar Pings') }}">
                        <span data-sound-icon>🔇</span> <span class="threat-btn-txt" data-sound-label>Mute</span>
                    </button>
                </div>

                {{-- Fullscreen Wallboard Button --}}
                <div class="threat-ctrl-group">
                    <button
                        type="button"
                        class="threat-fullscreen-btn"
                        data-map-fullscreen
                        title="{{ __('Tampilan Layar Penuh (SOC Wallboard)') }}"
                        aria-pressed="false"
                    >⛶ <span class="threat-fullscreen-text">{{ __('Full screen') }}</span></button>
                </div>
            </div>

            {{-- Severity Filter Bar Overlay (Bottom Left of Canvas) --}}
            <div class="threat-filter-overlay">
                <span class="threat-filter-title">{{ __('Filter Severity:') }}</span>
                <button type="button" class="threat-filter-pill active" data-filter-level="all">{{ __('ALL') }}</button>
                <button type="button" class="threat-filter-pill is-critical" data-filter-level="critical">● {{ __('CRITICAL') }}</button>
                <button type="button" class="threat-filter-pill is-high" data-filter-level="high">● {{ __('HIGH') }}</button>
                <button type="button" class="threat-filter-pill is-medium" data-filter-level="medium">● {{ __('MEDIUM') }}</button>
                <button type="button" class="threat-filter-pill is-low" data-filter-level="low">● {{ __('LOW') }}</button>
            </div>

            {{-- Target Crosshair Locator Label --}}
            <div class="threat-target-crosshair" data-target-crosshair style="display: none;">
                <div class="threat-crosshair-ring"></div>
                <div class="threat-crosshair-dot"></div>
                <div class="threat-crosshair-tag">{{ $target['name'] }}</div>
            </div>

            {{-- Offline / Fallback Notice (Non-blocking tactical toast) --}}
            <div class="threat-map-offline" data-map-offline hidden>
                <div class="threat-offline-box">
                    <span style="font-size: 20px;">🛰️</span>
                    <div>
                        <strong>{{ __('Mode Radar Mandiri (Offline Resilient)') }}</strong>
                        <p>{{ __('Visualisasi radar lokal aktif dengan kontur benua mandiri. Telemetri serangan dan mitigasi WAF tetap berjalan real-time.') }}</p>
                    </div>
                </div>
                <button type="button" class="threat-offline-close" data-offline-dismiss title="{{ __('Tutup') }}">✕</button>
            </div>
        </div>

        {{-- Tactical Sidebar (ThreatsEye Intelligence Telemetry) --}}
        <aside class="threat-map-side">
            {{-- Tabs Selector --}}
            <div class="threat-side-tabs">
                <button type="button" class="threat-tab-btn active" data-tab-target="live-feed">
                    <span class="threat-tab-dot"></span>
                    <span>{{ __('Live Feed') }}</span>
                </button>
                <button type="button" class="threat-tab-btn" data-tab-target="top-origins">
                    <span>{{ __('Top Origins') }}</span>
                </button>
                <button type="button" class="threat-tab-btn" data-tab-target="attack-vectors">
                    <span>{{ __('Vectors') }}</span>
                </button>
            </div>

            {{-- Tab 1: Live Incident Telemetry Feed --}}
            <div class="threat-tab-panel is-active" id="tab-live-feed">
                <div class="threat-panel-head">
                    <div class="threat-panel-title">
                        <span class="threat-pulse-cyan"></span>
                        <span>{{ __('Live Incident Telemetry') }}</span>
                    </div>
                    <span class="threat-panel-count" data-stream-count>0 events</span>
                </div>
                <div class="threat-log-scroll" data-map-logs>
                    {{-- Diisi secara dinamis oleh JavaScript dari data live real --}}
                </div>
            </div>

            {{-- Tab 2: Top Attacking Origins Leaderboard --}}
            <div class="threat-tab-panel" id="tab-top-origins" style="display: none;">
                <div class="threat-panel-head">
                    <div class="threat-panel-title">
                        <span>🌍</span>
                        <span>{{ __('Top Threat Origins') }}</span>
                    </div>
                    <span class="threat-panel-sub">{{ __('By Total Incidents') }}</span>
                </div>
                <div class="threat-origins-list" data-origins-list>
                    {{-- Diisi secara dinamis dari $payloadData['origins'] --}}
                </div>
            </div>

            {{-- Tab 3: Attack Vectors Breakdown --}}
            <div class="threat-tab-panel" id="tab-attack-vectors" style="display: none;">
                <div class="threat-panel-head">
                    <div class="threat-panel-title">
                        <span>⚡</span>
                        <span>{{ __('Attack Vectors Breakdown') }}</span>
                    </div>
                    <span class="threat-panel-sub">{{ __('Detected Signatures') }}</span>
                </div>
                <div class="threat-vectors-list" data-vectors-list>
                    {{-- Diisi secara dinamis dari sampel telemetri --}}
                </div>
            </div>
        </aside>
    </div>
</div>

{{-- ThreatsEye Tactical CSS Styling --}}
<style>
    /* ================= Cyber ThreatsEye Theme Palette ================= */
    .threat-map-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(6, 182, 212, 0.25);
        background: #020714;
        box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.8), 0 0 20px rgba(6, 182, 212, 0.08);
        color: #e2e8f0;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        position: relative;
    }

    /* 1. Top HUD Status Bar */
    .threat-hud-bar {
        background: rgba(3, 11, 25, 0.95);
        border-bottom: 1px solid rgba(6, 182, 212, 0.2);
        padding: 10px 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        backdrop-filter: blur(8px);
    }
    .threat-hud-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .threat-live-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 10px;
        border-radius: 6px;
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.3);
    }
    .threat-live-beacon {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 10px #10b981;
        animation: threat-radar-pulse 1.4s infinite ease-out;
    }
    .threat-live-label {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #10b981;
        text-transform: uppercase;
    }
    @keyframes threat-radar-pulse {
        0% { transform: scale(0.9); opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1.1); opacity: 0.8; box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.9); opacity: 1; box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .threat-target-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        background: rgba(6, 182, 212, 0.08);
        border: 1px solid rgba(6, 182, 212, 0.25);
        font-size: 11.5px;
    }
    .threat-target-name {
        color: #00f0ff;
        font-weight: 700;
    }
    .threat-target-coords {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 10px;
        color: #94a3b8;
    }

    .threat-mode-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .threat-mode-badge.is-enforcing {
        background: rgba(239, 68, 68, 0.12);
        border: 1px solid rgba(239, 68, 68, 0.35);
        color: #ef4444;
    }
    .threat-mode-badge.is-monitoring {
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.35);
        color: #fbbf24;
    }

    .threat-hud-right {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .threat-hud-clock {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12px;
        background: rgba(255, 255, 255, 0.04);
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: #cbd5e1;
    }
    .threat-clock-tz {
        font-size: 9.5px;
        color: #06b6d4;
        margin-left: 4px;
    }

    .threat-hud-stat {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        padding: 2px 8px;
        border-left: 1px solid rgba(255, 255, 255, 0.08);
    }
    .threat-hud-stat-label {
        font-size: 9.5px;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }
    .threat-hud-stat-val {
        font-size: 14px;
        font-weight: 800;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        line-height: 1.1;
    }
    .threat-hud-stat.is-danger .threat-hud-stat-val { color: #ef4444; text-shadow: 0 0 8px rgba(239, 68, 68, 0.4); }
    .threat-hud-stat.is-critical .threat-hud-stat-val { color: #f87171; }
    .threat-hud-stat.is-warning .threat-hud-stat-val { color: #fbbf24; }
    .threat-hud-stat.is-cyan .threat-hud-stat-val { color: #06b6d4; }

    /* 2. Main Viewport */
    .threat-map-wrap {
        display: flex;
        flex-direction: row;
        height: 640px;
        background: #020617;
        position: relative;
    }

    .threat-map-canvas-holder {
        position: relative;
        flex: 1;
        min-width: 0;
        min-height: 0;
        cursor: grab;
        overflow: hidden;
        touch-action: none;
        background: radial-gradient(circle at 50% 50%, #06152d 0%, #020617 80%);
    }
    .threat-map-canvas-holder:active { cursor: grabbing; }

    .threat-grid-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: 0.15;
        background-size: 40px 40px;
        background-image:
            linear-gradient(to right, rgba(6, 182, 212, 0.3) 1px, transparent 1px),
            linear-gradient(to bottom, rgba(6, 182, 212, 0.3) 1px, transparent 1px);
        z-index: 1;
    }

    .threat-map-canvas-holder canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
    }

    /* Floating Viewport Controls HUD */
    .threat-controls-hud {
        position: absolute;
        top: 14px;
        left: 14px;
        right: 14px;
        z-index: 5;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        pointer-events: none;
        flex-wrap: wrap;
    }
    .threat-ctrl-group {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(3, 11, 23, 0.85);
        border: 1px solid rgba(6, 182, 212, 0.3);
        border-radius: 8px;
        padding: 4px;
        backdrop-filter: blur(8px);
        pointer-events: auto;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
    }
    .threat-ctrl-btn {
        background: transparent;
        border: 1px solid transparent;
        color: #cbd5e1;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s ease;
        line-height: 1;
    }
    .threat-ctrl-btn:hover {
        background: rgba(6, 182, 212, 0.15);
        color: #00f0ff;
        border-color: rgba(6, 182, 212, 0.3);
    }
    .threat-ctrl-btn.active {
        background: rgba(6, 182, 212, 0.25);
        color: #00f0ff;
        border-color: #06b6d4;
        font-weight: 700;
        box-shadow: 0 0 10px rgba(6, 182, 212, 0.3);
    }

    .threat-fullscreen-btn {
        background: rgba(6, 182, 212, 0.15);
        border: 1px solid rgba(6, 182, 212, 0.4);
        color: #00f0ff;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .threat-fullscreen-btn:hover {
        background: rgba(6, 182, 212, 0.3);
        border-color: #00f0ff;
        box-shadow: 0 0 12px rgba(6, 182, 212, 0.4);
    }

    /* Severity Filter Overlay (Bottom Left) */
    .threat-filter-overlay {
        position: absolute;
        bottom: 14px;
        left: 14px;
        z-index: 5;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(3, 11, 23, 0.88);
        border: 1px solid rgba(6, 182, 212, 0.25);
        border-radius: 8px;
        padding: 5px 10px;
        backdrop-filter: blur(8px);
        font-size: 11px;
    }
    .threat-filter-title {
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 9.5px;
        margin-right: 2px;
    }
    .threat-filter-pill {
        border: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.04);
        color: #cbd5e1;
        font-size: 10.5px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .threat-filter-pill:hover { background: rgba(255, 255, 255, 0.1); }
    .threat-filter-pill.active {
        background: #06b6d4;
        color: #020813;
        border-color: #06b6d4;
        box-shadow: 0 0 8px rgba(6, 182, 212, 0.5);
    }
    .threat-filter-pill.is-critical { color: #f87171; }
    .threat-filter-pill.is-critical.active { background: #ef4444; color: #fff; border-color: #ef4444; box-shadow: 0 0 8px rgba(239, 68, 68, 0.5); }
    .threat-filter-pill.is-high { color: #fb923c; }
    .threat-filter-pill.is-high.active { background: #f97316; color: #fff; border-color: #f97316; box-shadow: 0 0 8px rgba(249, 115, 22, 0.5); }
    .threat-filter-pill.is-medium { color: #facc15; }
    .threat-filter-pill.is-medium.active { background: #eab308; color: #000; border-color: #eab308; box-shadow: 0 0 8px rgba(234, 179, 8, 0.5); }
    .threat-filter-pill.is-low { color: #34d399; }
    .threat-filter-pill.is-low.active { background: #10b981; color: #fff; border-color: #10b981; box-shadow: 0 0 8px rgba(16, 185, 129, 0.5); }

    /* Offline Notice (Non-blocking tactical toast) */
    .threat-map-offline {
        position: absolute;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 30;
        display: flex;
        align-items: center;
        gap: 14px;
        color: #e2e8f0;
        background: rgba(15, 23, 42, 0.96);
        border: 1px solid rgba(6, 182, 212, 0.4);
        border-radius: 8px;
        backdrop-filter: blur(8px);
        padding: 10px 16px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7);
        max-width: 90%;
        pointer-events: auto;
        animation: threatFadeIn 0.3s ease;
    }
    .threat-map-offline[hidden] {
        display: none !important;
    }
    .threat-offline-box {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
    }
    .threat-offline-box strong {
        color: #06b6d4;
        font-size: 12px;
        display: block;
        margin-bottom: 2px;
    }
    .threat-offline-box p {
        color: #94a3b8;
        font-size: 11px;
        margin: 0;
        line-height: 1.4;
    }
    .threat-offline-close {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #94a3b8;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        line-height: 1;
        padding: 6px 10px;
        border-radius: 6px;
        pointer-events: auto;
        position: relative;
        z-index: 31;
        transition: color 0.15s, background 0.15s, border-color 0.15s;
    }
    .threat-offline-close:hover {
        color: #fff;
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.5);
    }

    /* 3. Tactical Sidebar (ThreatsEye Intelligence) */
    .threat-map-side {
        width: 380px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        border-left: 1px solid rgba(6, 182, 212, 0.2);
        background: #020917;
        overflow: hidden;
    }

    .threat-side-tabs {
        display: flex;
        background: rgba(3, 11, 25, 0.9);
        border-bottom: 1px solid rgba(6, 182, 212, 0.2);
        padding: 6px 8px;
        gap: 6px;
    }
    .threat-tab-btn {
        flex: 1;
        background: transparent;
        border: 1px solid transparent;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        padding: 7px 6px;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: all 0.15s ease;
    }
    .threat-tab-btn:hover {
        color: #00f0ff;
        background: rgba(6, 182, 212, 0.08);
    }
    .threat-tab-btn.active {
        background: rgba(6, 182, 212, 0.18);
        color: #00f0ff;
        border-color: rgba(6, 182, 212, 0.35);
    }
    .threat-tab-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #ef4444;
        box-shadow: 0 0 6px #ef4444;
        animation: threat-radar-pulse 1.2s infinite ease-out;
    }

    .threat-tab-panel {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 12px;
    }
    .threat-panel-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 8px;
        margin-bottom: 8px;
        border-bottom: 1px solid rgba(6, 182, 212, 0.15);
    }
    .threat-panel-title {
        font-size: 11px;
        font-weight: 800;
        color: #00f0ff;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .threat-pulse-cyan {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #00f0ff;
        box-shadow: 0 0 8px #00f0ff;
    }
    .threat-panel-count, .threat-panel-sub {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 10px;
        color: #64748b;
    }

    /* Incident Stream Scroll */
    .threat-log-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-right: 4px;
        scrollbar-width: thin;
        scrollbar-color: #06b6d4 rgba(0, 0, 0, 0.3);
    }
    .threat-log-scroll::-webkit-scrollbar { width: 4px; }
    .threat-log-scroll::-webkit-scrollbar-thumb { background: #06b6d4; border-radius: 4px; }

    .threat-entry {
        background: rgba(15, 23, 42, 0.65);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-left: 3px solid #64748b;
        padding: 8px 10px;
        border-radius: 6px;
        font-size: 11px;
        color: #cbd5e1;
        animation: threat-slide-in 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        transition: transform 0.15s ease, background 0.15s ease;
    }
    .threat-entry:hover {
        background: rgba(15, 23, 42, 0.95);
        border-color: rgba(6, 182, 212, 0.3);
        transform: translateX(2px);
    }
    .threat-entry.l-critical { border-left-color: #ef4444; }
    .threat-entry.l-high { border-left-color: #f97316; }
    .threat-entry.l-medium { border-left-color: #eab308; }
    .threat-entry.l-low { border-left-color: #10b981; }

    .threat-entry-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }
    .threat-entry-time {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 10px;
        color: #94a3b8;
    }
    .threat-entry-badge {
        font-size: 9.5px;
        font-weight: 800;
        padding: 1px 5px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .threat-entry-badge.l-critical { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
    .threat-entry-badge.l-high { background: rgba(249, 115, 22, 0.2); color: #fb923c; border: 1px solid rgba(249, 115, 22, 0.4); }
    .threat-entry-badge.l-medium { background: rgba(234, 179, 8, 0.2); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.4); }
    .threat-entry-badge.l-low { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.4); }

    .threat-entry-body {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .threat-entry-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
    }
    .threat-entry-ip {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 700;
        color: #f1f5f9;
    }
    .threat-entry-place {
        color: #00f0ff;
        font-size: 10.5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }
    .threat-entry-desc {
        font-size: 11px;
        font-weight: 700;
        color: #f8fafc;
    }
    .threat-entry-path {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 10px;
        color: #94a3b8;
        word-break: break-all;
        background: rgba(0, 0, 0, 0.35);
        padding: 3px 6px;
        border-radius: 4px;
    }
    .threat-entry-status {
        font-size: 9.5px;
        font-weight: 800;
        letter-spacing: 0.05em;
    }
    .threat-entry-status.is-blocked { color: #10b981; }
    .threat-entry-status.is-logged { color: #fbbf24; }

    @keyframes threat-slide-in {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Top Origins & Vectors Lists */
    .threat-origins-list, .threat-vectors-list {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding-right: 4px;
    }
    .threat-rank-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 6px;
        padding: 8px 10px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: background 0.15s ease;
    }
    .threat-rank-card:hover { background: rgba(15, 23, 42, 0.9); border-color: rgba(6, 182, 212, 0.3); }
    .threat-rank-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 11.5px;
    }
    .threat-rank-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .threat-rank-idx {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 10px;
        font-weight: 800;
        color: #06b6d4;
        width: 16px;
    }
    .threat-rank-label {
        font-weight: 700;
        color: #f1f5f9;
    }
    .threat-rank-val {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 800;
        color: #00f0ff;
        font-size: 11px;
    }
    .threat-rank-bar-bg {
        height: 4px;
        background: rgba(255, 255, 255, 0.06);
        border-radius: 2px;
        overflow: hidden;
    }
    .threat-rank-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #06b6d4, #3b82f6);
        border-radius: 2px;
        transition: width 0.4s ease;
    }
    .threat-rank-bar-fill.is-red { background: linear-gradient(90deg, #f97316, #ef4444); }
    .threat-rank-bar-fill.is-yellow { background: linear-gradient(90deg, #eab308, #f59e0b); }

    /* 4. Fullscreen Wallboard Mode */
    .threat-map-card.is-fullscreen {
        position: fixed;
        inset: 0;
        z-index: 2147483000;
        width: 100vw;
        height: 100vh;
        margin: 0;
        border-radius: 0;
        border: none;
        display: flex;
        flex-direction: column;
    }
    .threat-map-card.is-fullscreen .threat-map-wrap {
        flex: 1;
        height: auto;
    }
    .threat-map-card.is-fullscreen .threat-map-side {
        width: 420px;
    }
    body.pasak-threat-fs {
        overflow: hidden !important;
    }

    /* 5. Responsive Breakpoints */
    @media (max-width: 1024px) {
        .threat-map-wrap {
            flex-direction: column;
            height: auto;
        }
        .threat-map-canvas-holder {
            height: 440px;
            min-height: 440px;
        }
        .threat-map-side {
            width: 100%;
            height: 380px;
            border-left: none;
            border-top: 1px solid rgba(6, 182, 212, 0.2);
        }
    }
    @media (max-width: 640px) {
        .threat-hud-bar { padding: 10px 12px; }
        .threat-target-coords { display: none; }
        .threat-controls-hud { top: 10px; left: 10px; right: 10px; }
        .threat-btn-txt { display: none; }
        .threat-filter-overlay { display: none; }
    }
</style>

{{-- D3.js and TopoJSON CDNs with resilient failover --}}
<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js" onerror="this.onerror=null;this.src='https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js';"></script>
<script src="https://cdn.jsdelivr.net/npm/topojson-client@3/dist/topojson-client.min.js" onerror="this.onerror=null;this.src='https://cdnjs.cloudflare.com/ajax/libs/topojson/3.0.2/topojson.min.js';"></script>

{{-- ThreatsEye Tactical Radar Engine Script --}}
<script>
(function () {
    'use strict';

    var card = document.querySelector('[data-threat-map]');
    if (!card) return;

    var MAP = @json($payload);
    var COLORS = {
        critical: '#ef4444',
        high: '#f97316',
        medium: '#eab308',
        low: '#10b981'
    };
    var LEVEL_ORDER = ['critical', 'high', 'medium', 'low'];

    var SCROLL = card.querySelector('[data-map-logs]');
    var ORIGINS_LIST = card.querySelector('[data-origins-list]');
    var VECTORS_LIST = card.querySelector('[data-vectors-list]');
    var STREAM_COUNT = card.querySelector('[data-stream-count]');

    var originsIndex = {};
    var vectorStats = {};
    var countryStats = {};
    var pendingLogs = [];
    var isGlobeReady = false;
    var lastId = 0;
    var totalLoggedIncidents = 0;
    var activeSeverityFilter = 'all';

    // Projection Mode: 'orthographic' (3D Globe) vs 'equirectangular' (2D Tactical Flat Map)
    var currentProjType = 'orthographic';
    var autoRotate = true;
    var soundEnabled = false;

    // Web Audio API for synthetic high-tech radar impact blips
    var audioCtx = null;
    function playRadarBlip(level) {
        if (!soundEnabled) return;
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();

            var osc = audioCtx.createOscillator();
            var gain = audioCtx.createGain();
            var freq = level === 'critical' ? 880 : (level === 'high' ? 660 : 440);

            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(freq / 2, audioCtx.currentTime + 0.08);

            gain.gain.setValueAtTime(0.04, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.08);

            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.08);
        } catch (e) { /* ignore audio failure */ }
    }

    // Live Clock Ticker (WIB + UTC)
    function updateClock() {
        var clockEl = card.querySelector('[data-threat-clock] .threat-clock-time');
        if (!clockEl) return;
        var now = new Date();
        var hours = String(now.getHours()).padStart(2, '0');
        var mins = String(now.getMinutes()).padStart(2, '0');
        var secs = String(now.getSeconds()).padStart(2, '0');
        clockEl.textContent = hours + ':' + mins + ':' + secs;
    }
    setInterval(updateClock, 1000);
    updateClock();

    function esc(val) {
        var d = document.createElement('div');
        d.textContent = val === null || val === undefined ? '' : String(val);
        return d.innerHTML;
    }

    function placeOf(location) {
        var p = [];
        if (location && location.place) p.push(location.place);
        if (location && location.country && location.place !== location.country) p.push(location.country);
        return p.length === 0 ? '{{ __("Unknown origin") }}' : p.join(', ');
    }

    function levelOf(entry) {
        return LEVEL_ORDER.indexOf(entry.level) === -1 ? 'low' : entry.level;
    }

    // Render an incident into the live telemetry feed
    function addLog(entry, location) {
        totalLoggedIncidents++;
        if (STREAM_COUNT) STREAM_COUNT.textContent = totalLoggedIncidents + ' events';

        // Track stats for vectors & countries
        var countryName = (location && location.country) ? location.country : 'Unknown';
        countryStats[countryName] = (countryStats[countryName] || 0) + 1;

        var vectorName = entry.desc || entry.event || 'Generic Probe';
        vectorStats[vectorName] = (vectorStats[vectorName] || 0) + 1;

        if (!SCROLL) return;

        var level = levelOf(entry);
        var timeStr = entry.time
            ? new Date(entry.time).toLocaleTimeString('id-ID', { hour12: false })
            : new Date().toLocaleTimeString('id-ID', { hour12: false });

        var el = document.createElement('div');
        el.className = 'threat-entry l-' + level;
        el.setAttribute('data-level', level);
        el.setAttribute('data-entry-id', entry.id || '');

        if (activeSeverityFilter !== 'all' && activeSeverityFilter !== level) {
            el.style.display = 'none';
        }

        var isBlocked = entry.blocked;
        var statusBadge = isBlocked
            ? '<span class="threat-entry-status is-blocked">● BLOCKED (403)</span>'
            : '<span class="threat-entry-status is-logged">● DETECTED</span>';

        el.innerHTML =
            '<div class="threat-entry-head">' +
                '<span class="threat-entry-time">[' + esc(timeStr) + ']</span>' +
                '<span class="threat-entry-badge l-' + level + '">' + esc(level.toUpperCase()) + '</span>' +
            '</div>' +
            '<div class="threat-entry-body">' +
                '<div class="threat-entry-meta">' +
                    '<span class="threat-entry-ip">' + esc(entry.ip) + '</span>' +
                    '<span class="threat-entry-place">📍 ' + esc(placeOf(location)) + '</span>' +
                '</div>' +
                '<div class="threat-entry-desc">' + esc(entry.desc || entry.event) + '</div>' +
                '<div class="threat-entry-path">&gt; ' + esc((entry.method || 'GET') + ' ' + (entry.path || '/')) + '</div>' +
                '<div style="display:flex; justify-content:space-between; align-items:center; margin-top:2px;">' +
                    statusBadge +
                    '<button type="button" class="threat-ctrl-btn" style="padding:2px 6px; font-size:10px;" onclick="openBlockModal && openBlockModal({ip:\'' + esc(entry.ip) + '\', reason:\'' + esc(entry.desc || entry.event) + '\'})">🚫 {{ __("Block IP") }}</button>' +
                '</div>' +
            '</div>';

        SCROLL.prepend(el);

        while (SCROLL.children.length > 50) {
            SCROLL.removeChild(SCROLL.lastChild);
        }

        renderLeaderboard();
        renderVectors();
    }

    // Render Leaderboard Top Attacking Origins
    function renderLeaderboard() {
        if (!ORIGINS_LIST) return;
        var sorted = Object.keys(countryStats).map(function (c) {
            return { country: c, count: countryStats[c] };
        }).sort(function (a, b) { return b.count - a.count; }).slice(0, 8);

        var max = sorted.length > 0 ? sorted[0].count : 1;
        var html = '';

        sorted.forEach(function (item, idx) {
            var pct = Math.round((item.count / max) * 100);
            html +=
                '<div class="threat-rank-card">' +
                    '<div class="threat-rank-top">' +
                        '<div class="threat-rank-left">' +
                            '<span class="threat-rank-idx">#' + (idx + 1) + '</span>' +
                            '<span class="threat-rank-label">' + esc(item.country) + '</span>' +
                        '</div>' +
                        '<span class="threat-rank-val">' + item.count + ' hits</span>' +
                    '</div>' +
                    '<div class="threat-rank-bar-bg">' +
                        '<div class="threat-rank-bar-fill' + (idx === 0 ? ' is-red' : '') + '" style="width:' + pct + '%;"></div>' +
                    '</div>' +
                '</div>';
        });

        ORIGINS_LIST.innerHTML = html || '<div style="color:#64748b; font-size:12px; padding:12px;">{{ __("Belum ada data serangan terdeteksi.") }}</div>';
    }

    // Render Attack Vectors Breakdown
    function renderVectors() {
        if (!VECTORS_LIST) return;
        var sorted = Object.keys(vectorStats).map(function (v) {
            return { vector: v, count: vectorStats[v] };
        }).sort(function (a, b) { return b.count - a.count; }).slice(0, 8);

        var max = sorted.length > 0 ? sorted[0].count : 1;
        var html = '';

        sorted.forEach(function (item, idx) {
            var pct = Math.round((item.count / max) * 100);
            html +=
                '<div class="threat-rank-card">' +
                    '<div class="threat-rank-top">' +
                        '<span class="threat-rank-label" style="font-size:11px;">' + esc(item.vector) + '</span>' +
                        '<span class="threat-rank-val" style="color:#f97316;">' + item.count + '</span>' +
                    '</div>' +
                    '<div class="threat-rank-bar-bg">' +
                        '<div class="threat-rank-bar-fill is-yellow" style="width:' + pct + '%;"></div>' +
                    '</div>' +
                '</div>';
        });

        VECTORS_LIST.innerHTML = html || '<div style="color:#64748b; font-size:12px; padding:12px;">{{ __("Belum ada jenis vektor teridentifikasi.") }}</div>';
    }

    // Seed data dari payload awal
    (MAP.origins || []).forEach(function (origin) {
        originsIndex[origin.ip] = origin;
        if (origin.country) {
            countryStats[origin.country] = (countryStats[origin.country] || 0) + (origin.hits || 1);
        }
        origin.samples.forEach(function (entry, index) {
            if (typeof entry.id === 'number' && entry.id > lastId) {
                lastId = entry.id;
            }
            if (index < 4) {
                pendingLogs.push({ level: entry.level, origin: origin.ip, entry: entry });
            }
        });
    });

    pendingLogs.forEach(function (item) {
        addLog(item.entry, originsIndex[item.origin]);
    });

    // Sidebar tab buttons switching
    card.querySelectorAll('[data-tab-target]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetId = 'tab-' + btn.getAttribute('data-tab-target');
            card.querySelectorAll('[data-tab-target]').forEach(function (b) { b.classList.remove('active'); });
            card.querySelectorAll('.threat-tab-panel').forEach(function (p) { p.style.display = 'none'; });

            btn.classList.add('active');
            var panel = card.querySelector('#' + targetId);
            if (panel) panel.style.display = 'flex';
        });
    });

    // Severity Filter Buttons
    card.querySelectorAll('[data-filter-level]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var lvl = btn.getAttribute('data-filter-level');
            activeSeverityFilter = lvl;
            card.querySelectorAll('[data-filter-level]').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            if (SCROLL) {
                SCROLL.querySelectorAll('.threat-entry').forEach(function (entryEl) {
                    var elLevel = entryEl.getAttribute('data-level');
                    entryEl.style.display = (lvl === 'all' || elLevel === lvl) ? 'block' : 'none';
                });
            }
        });
    });

    // Sound FX Button Toggle
    var soundBtn = card.querySelector('[data-map-sound]');
    if (soundBtn) {
        soundBtn.addEventListener('click', function () {
            soundEnabled = !soundEnabled;
            var icon = soundBtn.querySelector('[data-sound-icon]');
            var lbl = soundBtn.querySelector('[data-sound-label]');
            if (soundEnabled) {
                if (icon) icon.textContent = '🔊';
                if (lbl) lbl.textContent = 'Audio';
                soundBtn.classList.add('active');
                playRadarBlip('critical');
            } else {
                if (icon) icon.textContent = '🔇';
                if (lbl) lbl.textContent = 'Mute';
                soundBtn.classList.remove('active');
            }
        });
    }

    // Schedule initial animated arcs when globe is ready
    function scheduleInitialArcs() {
        if (!isGlobeReady || pendingLogs.length === 0) return;
        var shot = pendingLogs.shift();
        if (shot && originsIndex[shot.origin]) {
            window.__threatMapFire(MAP, originsIndex[shot.origin], shot.entry);
        }
        setTimeout(scheduleInitialArcs, 400 + Math.random() * 800);
    }

    // Polling Real-Time Feed via API
    function poll() {
        if (isGlobeReady) {
            var sep = String(MAP.feedUrl).indexOf('?') === -1 ? '?' : '&';
            fetch(MAP.feedUrl + sep + 'after=' + lastId, { credentials: 'same-origin' }).then(function (res) {
                return res.ok ? res.json() : null;
            }).then(function (data) {
                if (!data) return;
                if (typeof data.last_id === 'number' && data.last_id > lastId) {
                    lastId = data.last_id;
                }
                var attacksEl = card.querySelector('[data-map-attacks]');
                if (attacksEl && data.stats && data.stats.logs_today !== undefined) {
                    attacksEl.innerText = Number(data.stats.logs_today || 0).toLocaleString('id-ID');
                }
                if (data.logs && Array.isArray(data.logs)) {
                    data.logs.forEach(function (entry) {
                        var loc = (data.locations && data.locations[entry.ip]) ? data.locations[entry.ip] : null;
                        if (loc && loc.lat !== null && !originsIndex[entry.ip]) {
                            originsIndex[entry.ip] = {
                                ip: entry.ip,
                                lat: loc.lat,
                                lon: loc.lon,
                                place: loc.place,
                                country: loc.country
                            };
                        }
                        addLog(entry, loc || originsIndex[entry.ip]);
                        if (isGlobeReady && originsIndex[entry.ip]) {
                            window.__threatMapFire(MAP, originsIndex[entry.ip], entry);
                        }
                    });
                }
            }).catch(function () { /* retry next interval */ });
        }
        setTimeout(poll, (MAP.refresh || 6) * 1000);
    }
    poll();

    // ==================== Dual-Engine Tactical Radar System ====================
    var onEngineResize = null;

    // Dismiss offline toast
    var offlineNotice = card.querySelector('[data-map-offline]');
    var offlineClose = card.querySelector('[data-offline-dismiss]');
    if (offlineClose && offlineNotice) {
        var dismissOffline = function (e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            offlineNotice.hidden = true;
            offlineNotice.setAttribute('hidden', '');
            offlineNotice.style.display = 'none';
        };
        offlineClose.addEventListener('click', dismissOffline);
        offlineClose.addEventListener('mousedown', function (e) { e.stopPropagation(); });
        offlineClose.addEventListener('pointerdown', function (e) { e.stopPropagation(); });
    }

    // Embedded lightweight GeoJSON continents geometry fallback (100% offline resilience)
    var FALLBACK_WORLD_GEOJSON = {
        type: 'FeatureCollection',
        features: [
            {
                type: 'Feature',
                properties: { name: 'North America' },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [-168, 65], [-160, 70], [-125, 72], [-85, 76], [-75, 72], [-60, 60],
                        [-65, 45], [-75, 35], [-80, 25], [-90, 20], [-105, 20], [-120, 32],
                        [-125, 40], [-130, 50], [-140, 58], [-160, 58], [-168, 65]
                    ]]
                }
            },
            {
                type: 'Feature',
                properties: { name: 'South America' },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [-80, 8], [-60, 10], [-50, 0], [-35, -5], [-38, -15], [-50, -30],
                        [-60, -40], [-68, -55], [-75, -50], [-72, -35], [-80, -5], [-80, 8]
                    ]]
                }
            },
            {
                type: 'Feature',
                properties: { name: 'Eurasia' },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [-10, 36], [0, 42], [10, 44], [30, 46], [40, 40], [50, 42], [60, 38],
                        [70, 25], [80, 15], [90, 22], [100, 10], [105, 20], [120, 22], [122, 32],
                        [130, 40], [140, 48], [170, 65], [180, 68], [140, 72], [90, 74], [40, 70],
                        [20, 70], [5, 62], [-5, 52], [-10, 36]
                    ]]
                }
            },
            {
                type: 'Feature',
                properties: { name: 'Africa' },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [-17, 15], [-5, 36], [10, 37], [25, 32], [32, 31], [43, 12], [51, 12],
                        [40, -10], [32, -28], [28, -34], [18, -34], [12, -15], [5, 5], [-15, 10], [-17, 15]
                    ]]
                }
            },
            {
                type: 'Feature',
                properties: { name: 'Australia' },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [114, -22], [124, -15], [135, -12], [142, -11], [146, -20], [153, -28],
                        [150, -37], [140, -38], [130, -32], [116, -35], [114, -22]
                    ]]
                }
            },
            {
                type: 'Feature',
                properties: { name: 'Southeast Asia & Indonesia' },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [95, 5], [105, 5], [115, 7], [118, -4], [125, 2], [130, -3],
                        [140, -3], [140, -8], [120, -10], [106, -7], [96, 3], [95, 5]
                    ]]
                }
            },
            {
                type: 'Feature',
                properties: { name: 'Antarctica' },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [-180, -65], [-120, -68], [-60, -65], [0, -68], [60, -65], [120, -68], [180, -65], [180, -88], [-180, -88], [-180, -65]
                    ]]
                }
            }
        ]
    };

    var worldData = FALLBACK_WORLD_GEOJSON;

    function loadWorldAtlas(onReady) {
        var jsdelivrUrl = 'https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json';
        var unpkgUrl = 'https://unpkg.com/world-atlas@2/countries-110m.json';

        function fetchWithTimeout(url, ms) {
            return new Promise(function (resolve, reject) {
                var timer = setTimeout(function () { reject(new Error('timeout')); }, ms);
                fetch(url).then(function (res) {
                    clearTimeout(timer);
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                }).then(resolve).catch(function (err) {
                    clearTimeout(timer);
                    reject(err);
                });
            });
        }

        fetchWithTimeout(jsdelivrUrl, 3500)
            .then(function (data) {
                if (typeof topojson !== 'undefined' && data.objects && data.objects.countries) {
                    worldData = topojson.feature(data, data.objects.countries);
                } else {
                    worldData = data;
                }
                onReady(false);
            })
            .catch(function () {
                fetchWithTimeout(unpkgUrl, 3500)
                    .then(function (data2) {
                        if (typeof topojson !== 'undefined' && data2.objects && data2.objects.countries) {
                            worldData = topojson.feature(data2, data2.objects.countries);
                        } else {
                            worldData = data2;
                        }
                        onReady(false);
                    })
                    .catch(function () {
                        worldData = FALLBACK_WORLD_GEOJSON;
                        onReady(true);
                    });
            });
    }

    // Engine 1: D3.js 3D Globe & 2D Tactical Vector Engine
    function initD3GlobeEngine() {
        var holder = card.querySelector('.threat-map-canvas-holder');
        var baseCanvas = card.querySelector('[data-map-base]');
        var animCanvas = card.querySelector('[data-map-anim]');
        var baseCtx = baseCanvas.getContext('2d');
        var animCtx = animCanvas.getContext('2d');

        var width, height, projection, geoPath;
        var centerX, centerY;
        var baseScale = 1;
        var currentZoom = 1;
        var activeAttacks = [];
        var ripples = [];
        var graticule = d3.geoGraticule();

        function createProjection(type) {
            if (type === 'equirectangular') {
                return d3.geoEquirectangular()
                    .translate([centerX, centerY])
                    .scale(baseScale * 1.55 * currentZoom);
            }
            return d3.geoOrthographic()
                .translate([centerX, centerY])
                .scale(baseScale * currentZoom)
                .rotate([-MAP.target.lon, -MAP.target.lat, 0]);
        }

        // Projection Mode Buttons
        card.querySelectorAll('[data-map-proj]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var mode = btn.getAttribute('data-map-proj');
                currentProjType = mode;
                card.querySelectorAll('[data-map-proj]').forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');

                projection = createProjection(currentProjType);
                geoPath = d3.geoPath().projection(projection).context(baseCtx);
                drawBaseMap();
            });
        });

        // Zoom & Pan behaviors
        var zoom = d3.zoom()
            .scaleExtent([0.6, 6])
            .on('zoom', function (event) {
                currentZoom = event.transform.k;
                if (currentProjType === 'equirectangular') {
                    projection.scale(baseScale * 1.55 * currentZoom);
                } else {
                    projection.scale(baseScale * currentZoom);
                }
                drawBaseMap();
            });

        var drag = d3.drag()
            .on('drag', function (event) {
                if (currentProjType === 'orthographic') {
                    var rot = projection.rotate();
                    var k = 70 / projection.scale();
                    projection.rotate([rot[0] + event.dx * k, rot[1] - event.dy * k, rot[2]]);
                } else {
                    var t = projection.translate();
                    projection.translate([t[0] + event.dx, t[1] + event.dy]);
                }
                drawBaseMap();
            });

        d3.select(holder).call(drag).call(zoom);

        // Zoom and Action Controls
        card.querySelectorAll('[data-map-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.getAttribute('data-map-action');
                if (action === 'zoom-in') {
                    d3.select(holder).transition().duration(250).call(zoom.scaleBy, 1.3);
                } else if (action === 'zoom-out') {
                    d3.select(holder).transition().duration(250).call(zoom.scaleBy, 0.75);
                } else if (action === 'reset') {
                    currentZoom = 1;
                    d3.select(holder).transition().duration(350).call(zoom.transform, d3.zoomIdentity);
                    if (currentProjType === 'orthographic') {
                        projection.rotate([-MAP.target.lon, -MAP.target.lat, 0]);
                    } else {
                        projection.translate([centerX, centerY]);
                    }
                    drawBaseMap();
                } else if (action === 'toggle-rotate') {
                    autoRotate = !autoRotate;
                    btn.classList.toggle('active', autoRotate);
                }
            });
        });

        function resize() {
            var rect = holder.getBoundingClientRect();
            var dpr = window.devicePixelRatio || 1;
            width = rect.width;
            height = rect.height;

            [baseCanvas, animCanvas].forEach(function (c) {
                c.width = width * dpr;
                c.height = height * dpr;
                c.getContext('2d').scale(dpr, dpr);
            });

            centerX = width / 2;
            centerY = height / 2;
            baseScale = Math.min(width, height) / 2.3;

            projection = createProjection(currentProjType);
            geoPath = d3.geoPath().projection(projection).context(baseCtx);

            if (worldData) drawBaseMap();
        }

        window.addEventListener('resize', resize);
        if (typeof ResizeObserver === 'function') {
            new ResizeObserver(resize).observe(holder);
        }
        onEngineResize = resize;

        function isVisible(coords) {
            if (currentProjType === 'equirectangular') return true;
            var rot = projection.rotate();
            var centerLon = -rot[0];
            var centerLat = -rot[1];
            return d3.geoDistance(coords, [centerLon, centerLat]) < Math.PI / 2;
        }

        function getElevatedPoint(geoCoords, progress) {
            var pt = projection(geoCoords);
            if (!pt) return null;
            if (currentProjType === 'equirectangular') {
                var arcElevation = Math.sin(progress * Math.PI) * 45 * currentZoom;
                return [pt[0], pt[1] - arcElevation];
            }
            var dx = pt[0] - centerX;
            var dy = pt[1] - centerY;
            var distFromCenter = Math.sqrt(dx * dx + dy * dy);
            var globeRadius = projection.scale();
            var altitude = Math.sin(progress * Math.PI) * (globeRadius * 0.22);
            var factor = distFromCenter > 0 ? (distFromCenter + altitude) / distFromCenter : 1;
            return [centerX + dx * factor, centerY + dy * factor];
        }

        function drawBaseMap() {
            baseCtx.clearRect(0, 0, width, height);

            // 3D Globe Sphere Background
            if (currentProjType === 'orthographic') {
                baseCtx.beginPath();
                baseCtx.arc(centerX, centerY, projection.scale(), 0, Math.PI * 2);
                var oceanGrad = baseCtx.createRadialGradient(centerX - projection.scale() * 0.3, centerY - projection.scale() * 0.3, 10, centerX, centerY, projection.scale());
                oceanGrad.addColorStop(0, '#041530');
                oceanGrad.addColorStop(1, '#010816');
                baseCtx.fillStyle = oceanGrad;
                baseCtx.fill();
            }

            // Graticule Lines
            baseCtx.beginPath();
            geoPath(graticule());
            baseCtx.strokeStyle = 'rgba(6, 182, 212, 0.08)';
            baseCtx.lineWidth = 0.5;
            baseCtx.stroke();

            // World Countries Fill & Outlines
            if (worldData) {
                baseCtx.beginPath();
                geoPath(worldData);
                baseCtx.fillStyle = '#0a1d3d';
                baseCtx.fill();

                baseCtx.strokeStyle = '#06b6d4';
                baseCtx.lineWidth = 0.8;
                baseCtx.shadowColor = 'rgba(6, 182, 212, 0.5)';
                baseCtx.shadowBlur = 4;
                baseCtx.lineJoin = 'round';
                baseCtx.stroke();
                baseCtx.shadowBlur = 0;
            }

            // 3D Globe Outer Atmosphere Rim Glow
            if (currentProjType === 'orthographic') {
                baseCtx.beginPath();
                baseCtx.arc(centerX, centerY, projection.scale(), 0, Math.PI * 2);
                baseCtx.strokeStyle = 'rgba(0, 240, 255, 0.5)';
                baseCtx.lineWidth = 1.2;
                baseCtx.shadowColor = 'rgba(0, 240, 255, 0.6)';
                baseCtx.shadowBlur = 24;
                baseCtx.stroke();
                baseCtx.shadowBlur = 0;
            }

            // Target Server Node Marker (Pekanbaru / configured target)
            var targetPoint = projection([MAP.target.lon, MAP.target.lat]);
            if (targetPoint && isVisible([MAP.target.lon, MAP.target.lat])) {
                // Concentric Radar Rings
                baseCtx.beginPath();
                baseCtx.arc(targetPoint[0], targetPoint[1], 12 * currentZoom, 0, Math.PI * 2);
                baseCtx.strokeStyle = 'rgba(0, 240, 255, 0.35)';
                baseCtx.lineWidth = 1;
                baseCtx.stroke();

                baseCtx.beginPath();
                baseCtx.arc(targetPoint[0], targetPoint[1], 6 * currentZoom, 0, Math.PI * 2);
                baseCtx.fillStyle = '#00f0ff';
                baseCtx.shadowColor = '#00f0ff';
                baseCtx.shadowBlur = 15;
                baseCtx.fill();
                baseCtx.shadowBlur = 0;

                // Target Crosshair Center
                baseCtx.beginPath();
                baseCtx.arc(targetPoint[0], targetPoint[1], 2 * currentZoom, 0, Math.PI * 2);
                baseCtx.fillStyle = '#ffffff';
                baseCtx.fill();
            }
        }

        // Fire an animated attack trajectory
        window.__threatMapFire = function (map, origin, entry) {
            if (!origin || typeof origin.lat !== 'number' || typeof origin.lon !== 'number') return;
            var lvl = levelOf(entry);
            var jitter = 2;
            activeAttacks.push({
                sourceCoords: [origin.lon + (Math.random() - 0.5) * jitter, origin.lat + (Math.random() - 0.5) * jitter],
                targetCoords: [map.target.lon, map.target.lat],
                progress: 0,
                speed: 0.006 + Math.random() * 0.005,
                color: COLORS[lvl] || '#ef4444',
                level: lvl
            });
        };

        // Animation Loop (Particle Arcs + Ripple Shockwaves)
        var lastSpinTime = Date.now();
        function animate() {
            animCtx.clearRect(0, 0, width, height);

            // Smooth Auto-Rotate when in 3D Orthographic mode and not dragging
            if (autoRotate && currentProjType === 'orthographic' && isGlobeReady) {
                var now = Date.now();
                if (now - lastSpinTime > 40) {
                    var rot = projection.rotate();
                    projection.rotate([rot[0] + 0.15, rot[1], rot[2]]);
                    drawBaseMap();
                    lastSpinTime = now;
                }
            }

            // Draw Shockwave Ripples at Server Target
            for (var r = ripples.length - 1; r >= 0; r--) {
                var rip = ripples[r];
                rip.radius += rip.speed;
                rip.alpha -= rip.fade;
                if (rip.alpha <= 0) {
                    ripples.splice(r, 1);
                    continue;
                }
                var tPt = projection([MAP.target.lon, MAP.target.lat]);
                if (tPt && isVisible([MAP.target.lon, MAP.target.lat])) {
                    animCtx.beginPath();
                    animCtx.arc(tPt[0], tPt[1], rip.radius, 0, Math.PI * 2);
                    animCtx.strokeStyle = 'rgba(' + rip.rgb + ', ' + rip.alpha + ')';
                    animCtx.lineWidth = 1.8;
                    animCtx.stroke();
                }
            }

            // Draw Animated Cyber Attack Arcs
            for (var i = activeAttacks.length - 1; i >= 0; i--) {
                var atk = activeAttacks[i];
                atk.progress += atk.speed;

                if (atk.progress >= 1) {
                    // Detonation ripple at server node
                    var rgbMap = {
                        '#ef4444': '239, 68, 68',
                        '#f97316': '249, 115, 22',
                        '#eab308': '234, 179, 8',
                        '#10b981': '16, 185, 129'
                    };
                    ripples.push({
                        radius: 5 * currentZoom,
                        speed: 1.8 * currentZoom,
                        alpha: 1,
                        fade: 0.04,
                        rgb: rgbMap[atk.color] || '239, 68, 68'
                    });
                    playRadarBlip(atk.level);
                    activeAttacks.splice(i, 1);
                    continue;
                }

                var interpolator = d3.geoInterpolate(atk.sourceCoords, atk.targetCoords);
                var currentGeo = interpolator(atk.progress);

                // Draw glowing comet head
                if (isVisible(currentGeo)) {
                    var head = getElevatedPoint(currentGeo, atk.progress);
                    if (head) {
                        animCtx.beginPath();
                        animCtx.arc(head[0], head[1], 3.5 * currentZoom, 0, Math.PI * 2);
                        animCtx.fillStyle = '#ffffff';
                        animCtx.shadowColor = atk.color;
                        animCtx.shadowBlur = 10;
                        animCtx.fill();
                        animCtx.shadowBlur = 0;
                    }
                }

                // Draw arched laser beam trail
                var trailSegments = 24;
                var trailLength = 0.16;
                for (var j = 0; j < trailSegments; j++) {
                    var t1 = atk.progress - (j / trailSegments) * trailLength;
                    var t2 = atk.progress - ((j + 1) / trailSegments) * trailLength;
                    if (t1 <= 0) break;
                    t2 = Math.max(0, t2);

                    var g1 = interpolator(t1);
                    var g2 = interpolator(t2);
                    if (!isVisible(g1) && !isVisible(g2)) continue;

                    var p1 = getElevatedPoint(g1, t1);
                    var p2 = getElevatedPoint(g2, t2);
                    if (!p1 || !p2) continue;

                    animCtx.beginPath();
                    animCtx.moveTo(p1[0], p1[1]);
                    animCtx.lineTo(p2[0], p2[1]);
                    animCtx.strokeStyle = atk.color;
                    animCtx.lineWidth = Math.max(0.6, 4.0 - j * 0.16) * currentZoom;
                    animCtx.globalAlpha = Math.max(0, 1 - j / trailSegments);

                    if (j === 0) {
                        animCtx.shadowBlur = 12;
                        animCtx.shadowColor = atk.color;
                    } else {
                        animCtx.shadowBlur = 0;
                    }
                    animCtx.stroke();
                    animCtx.globalAlpha = 1;
                }
            }

            requestAnimationFrame(animate);
        }

        resize();
        isGlobeReady = true;
        animate();
        scheduleInitialArcs();

        // Asynchronously load high-detail 110m world atlas
        loadWorldAtlas(function (isFallback) {
            drawBaseMap();
            if (isFallback && offlineNotice) {
                offlineNotice.hidden = false;
                offlineNotice.removeAttribute('hidden');
                offlineNotice.style.display = 'flex';
            } else if (offlineNotice) {
                offlineNotice.hidden = true;
                offlineNotice.setAttribute('hidden', '');
                offlineNotice.style.display = 'none';
            }
        });
    }

    // Engine 2: Native Canvas 2D Cyber Radar Engine (Offline Standalone Mode)
    function initFallbackRadarEngine() {
        var holder = card.querySelector('.threat-map-canvas-holder');
        var baseCanvas = card.querySelector('[data-map-base]');
        var animCanvas = card.querySelector('[data-map-anim]');
        var baseCtx = baseCanvas.getContext('2d');
        var animCtx = animCanvas.getContext('2d');
        var width, height, centerX, centerY, radius;
        var radarZoom = 1;
        var sweepAngle = 0;
        var activeAttacks = [];
        var ripples = [];

        // Show offline notice pill
        if (offlineNotice) {
            offlineNotice.hidden = false;
            offlineNotice.removeAttribute('hidden');
            offlineNotice.style.display = 'flex';
        }

        function resize() {
            var rect = holder.getBoundingClientRect();
            var dpr = window.devicePixelRatio || 1;
            width = rect.width;
            height = rect.height;

            [baseCanvas, animCanvas].forEach(function (c) {
                c.width = width * dpr;
                c.height = height * dpr;
                c.getContext('2d').scale(dpr, dpr);
            });

            centerX = width / 2;
            centerY = height / 2;
            radius = Math.min(width, height) * 0.44;
            drawBaseRadar();
        }

        window.addEventListener('resize', resize);
        if (typeof ResizeObserver === 'function') {
            new ResizeObserver(resize).observe(holder);
        }
        onEngineResize = resize;

        // Action Controls for Fallback Radar
        card.querySelectorAll('[data-map-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.getAttribute('data-map-action');
                if (action === 'zoom-in') {
                    radarZoom = Math.min(2.5, radarZoom * 1.25);
                    drawBaseRadar();
                } else if (action === 'zoom-out') {
                    radarZoom = Math.max(0.6, radarZoom * 0.8);
                    drawBaseRadar();
                } else if (action === 'reset') {
                    radarZoom = 1;
                    drawBaseRadar();
                }
            });
        });

        function drawBaseRadar() {
            baseCtx.clearRect(0, 0, width, height);

            // Radar background gradient
            var bgGrad = baseCtx.createRadialGradient(centerX, centerY, 0, centerX, centerY, radius * radarZoom);
            bgGrad.addColorStop(0, '#041738');
            bgGrad.addColorStop(0.8, '#020b1c');
            bgGrad.addColorStop(1, '#01050e');
            baseCtx.fillStyle = bgGrad;
            baseCtx.beginPath();
            baseCtx.arc(centerX, centerY, radius * radarZoom, 0, Math.PI * 2);
            baseCtx.fill();

            // Range rings
            var rings = [0.25, 0.5, 0.75, 1.0];
            rings.forEach(function (pct) {
                var r = radius * radarZoom * pct;
                baseCtx.beginPath();
                baseCtx.arc(centerX, centerY, r, 0, Math.PI * 2);
                baseCtx.strokeStyle = 'rgba(6, 182, 212, ' + (pct === 1.0 ? '0.45' : '0.15') + ')';
                baseCtx.lineWidth = pct === 1.0 ? 1.5 : 0.8;
                if (pct < 1.0) baseCtx.setLineDash([4, 4]);
                baseCtx.stroke();
                baseCtx.setLineDash([]);
            });

            // Crosshair Axes (N-S, E-W)
            baseCtx.beginPath();
            baseCtx.moveTo(centerX - radius * radarZoom, centerY);
            baseCtx.lineTo(centerX + radius * radarZoom, centerY);
            baseCtx.moveTo(centerX, centerY - radius * radarZoom);
            baseCtx.lineTo(centerX, centerY + radius * radarZoom);
            baseCtx.strokeStyle = 'rgba(6, 182, 212, 0.25)';
            baseCtx.lineWidth = 1;
            baseCtx.stroke();

            // Diagonal Crosshairs
            var diag = radius * radarZoom * 0.7071;
            baseCtx.beginPath();
            baseCtx.moveTo(centerX - diag, centerY - diag);
            baseCtx.lineTo(centerX + diag, centerY + diag);
            baseCtx.moveTo(centerX - diag, centerY + diag);
            baseCtx.lineTo(centerX + diag, centerY - diag);
            baseCtx.strokeStyle = 'rgba(6, 182, 212, 0.1)';
            baseCtx.lineWidth = 0.6;
            baseCtx.stroke();

            // Cardinal labels
            baseCtx.font = '10px monospace';
            baseCtx.fillStyle = '#06b6d4';
            baseCtx.textAlign = 'center';
            baseCtx.fillText('N', centerX, centerY - radius * radarZoom + 14);
            baseCtx.fillText('S', centerX, centerY + radius * radarZoom - 6);
            baseCtx.fillText('E', centerX + radius * radarZoom - 12, centerY + 3);
            baseCtx.fillText('W', centerX - radius * radarZoom + 12, centerY + 3);

            // Target Node Marker Center
            baseCtx.beginPath();
            baseCtx.arc(centerX, centerY, 5, 0, Math.PI * 2);
            baseCtx.fillStyle = '#00f0ff';
            baseCtx.shadowColor = '#00f0ff';
            baseCtx.shadowBlur = 12;
            baseCtx.fill();
            baseCtx.shadowBlur = 0;
        }

        // Relative Geo to Polar Radar Mapping
        function geoToRadar(lon, lat) {
            var dLon = (lon - MAP.target.lon) * Math.PI / 180;
            var dLat = (lat - MAP.target.lat) * Math.PI / 180;
            var dist = Math.min(1, Math.sqrt(dLon * dLon + dLat * dLat) / (Math.PI / 1.3));
            var angle = Math.atan2(dLat, dLon);
            var r = dist * radius * radarZoom * 0.88;
            return [centerX + r * Math.cos(angle), centerY - r * Math.sin(angle)];
        }

        window.__threatMapFire = function (map, origin, entry) {
            if (!origin || typeof origin.lat !== 'number' || typeof origin.lon !== 'number') return;
            var pt = geoToRadar(origin.lon, origin.lat);
            var lvl = levelOf(entry);
            activeAttacks.push({
                startX: pt[0],
                startY: pt[1],
                targetX: centerX,
                targetY: centerY,
                progress: 0,
                speed: 0.008 + Math.random() * 0.006,
                color: COLORS[lvl] || '#ef4444',
                level: lvl
            });
        };

        function animate() {
            animCtx.clearRect(0, 0, width, height);

            sweepAngle = (sweepAngle + 0.02) % (Math.PI * 2);

            // Radar sweep sector
            var sweepGrad = animCtx.createRadialGradient(centerX, centerY, 0, centerX, centerY, radius * radarZoom);
            sweepGrad.addColorStop(0, 'rgba(6, 182, 212, 0.25)');
            sweepGrad.addColorStop(1, 'rgba(6, 182, 212, 0.0)');
            animCtx.beginPath();
            animCtx.moveTo(centerX, centerY);
            animCtx.arc(centerX, centerY, radius * radarZoom, sweepAngle - 0.28, sweepAngle);
            animCtx.closePath();
            animCtx.fillStyle = sweepGrad;
            animCtx.fill();

            // Active attack beams
            for (var i = activeAttacks.length - 1; i >= 0; i--) {
                var atk = activeAttacks[i];
                atk.progress += atk.speed;

                if (atk.progress >= 1) {
                    ripples.push({
                        x: centerX,
                        y: centerY,
                        r: 4,
                        alpha: 1,
                        color: atk.color
                    });
                    playRadarBlip(atk.level);
                    activeAttacks.splice(i, 1);
                    continue;
                }

                var curX = atk.startX + (atk.targetX - atk.startX) * atk.progress;
                var curY = atk.startY + (atk.targetY - atk.startY) * atk.progress;

                animCtx.beginPath();
                animCtx.moveTo(atk.startX, atk.startY);
                animCtx.lineTo(curX, curY);
                animCtx.strokeStyle = atk.color;
                animCtx.lineWidth = 2;
                animCtx.stroke();

                animCtx.beginPath();
                animCtx.arc(curX, curY, 3.5, 0, Math.PI * 2);
                animCtx.fillStyle = '#ffffff';
                animCtx.shadowColor = atk.color;
                animCtx.shadowBlur = 10;
                animCtx.fill();
                animCtx.shadowBlur = 0;
            }

            // Shockwave ripples
            for (var j = ripples.length - 1; j >= 0; j--) {
                var rip = ripples[j];
                rip.r += 1.4;
                rip.alpha -= 0.035;
                if (rip.alpha <= 0) {
                    ripples.splice(j, 1);
                    continue;
                }
                animCtx.beginPath();
                animCtx.arc(rip.x, rip.y, rip.r, 0, Math.PI * 2);
                animCtx.strokeStyle = rip.color;
                animCtx.globalAlpha = rip.alpha;
                animCtx.lineWidth = 2;
                animCtx.stroke();
                animCtx.globalAlpha = 1;
            }

            requestAnimationFrame(animate);
        }

        resize();
        animate();
        isGlobeReady = true;
        scheduleInitialArcs();
    }

    // Initialize Active Engine with retry timer for D3 loading
    function bootEngine() {
        if (typeof d3 !== 'undefined' && typeof d3.geoOrthographic === 'function') {
            initD3GlobeEngine();
        } else {
            var retries = 0;
            var timer = setInterval(function () {
                retries++;
                if (typeof d3 !== 'undefined' && typeof d3.geoOrthographic === 'function') {
                    clearInterval(timer);
                    initD3GlobeEngine();
                } else if (retries >= 25) { // 2.5s fallback
                    clearInterval(timer);
                    initFallbackRadarEngine();
                }
            }, 100);
        }
    }
    bootEngine();

    // ==================== Fullscreen Wallboard Mode ====================
    var fullButton = card.querySelector('[data-map-fullscreen]');
    function toggleFullscreen(active) {
        card.classList.toggle('is-fullscreen', active);
        document.body.classList.toggle('pasak-threat-fs', active);
        if (fullButton) {
            fullButton.setAttribute('aria-pressed', active ? 'true' : 'false');
            var text = active ? '{{ __("Exit full screen") }}' : '{{ __("Full screen") }}';
            fullButton.title = text;
            var textEl = fullButton.querySelector('.threat-fullscreen-text');
            if (textEl) textEl.textContent = text;
            fullButton.blur();
        }
        if (typeof onEngineResize === 'function') onEngineResize();
    }

    if (fullButton) {
        fullButton.addEventListener('click', function () {
            toggleFullscreen(!card.classList.contains('is-fullscreen'));
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && card.classList.contains('is-fullscreen')) {
            toggleFullscreen(false);
        }
    });
})();
</script>
@endsection
