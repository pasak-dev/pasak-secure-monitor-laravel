{{--
    Halaman Rate Limit / Cooldown Login Bertingkat (HTTP 429) untuk pasax/secure-monitor.

    Dipublikasikan oleh:  php artisan vendor:publish --tag=pasax-views
    Tujuan default:       resources/views/errors/pasax-login-throttle.blade.php

    Prinsip: 100% Pure PHP / Zero NPM, inline CSS + vanilla JS responsif.
    Seluruh selector CSS dan ID menggunakan prefix unik 'pasax-' untuk mencegah
    konflik dengan stylesheet / framework frontend aplikasi pengguna.

    Variabel yang tersedia:
    $seconds, $lockoutLevel, $message, $email, $ip, $decayHours, $retryUrl
--}}
@php
    $sec = max(1, (int) ($seconds ?? 30));
    $level = max(1, (int) ($lockoutLevel ?? 1));
    $msg = $message ?? __('Terlalu banyak percobaan login yang gagal. Akun Anda sedang dalam masa jeda pengamanan (cooldown).');
    $userEmail = $email ?? __('Kredensial Pengguna');
    $clientIp = $ip ?? request()->ip();
    $cycleHours = max(1, (int) ($decayHours ?? 6));
    $targetUrl = $retryUrl ?? ((url()->previous() && url()->previous() !== url()->current()) ? url()->previous() : url('/'));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>({{ $sec }}s) {{ __('Akses Login Dibatasi — Pasax') }}</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Cdefs%3E%3ClinearGradient id='pasaxFav' x1='0%25' y1='0%25' x2='100%25' y2='100%25'%3E%3Cstop offset='0%25' stop-color='%233b82f6'/%3E%3Cstop offset='100%25' stop-color='%231d4ed8'/%3E%3C/linearGradient%3E%3C/defs%3E%3Cpath d='M16 3L5 7.5v8.5c0 6.6 4.7 12.8 11 14 6.3-1.2 11-7.4 11-14V7.5L16 3z' fill='url(%23pasaxFav)'/%3E%3Cpath d='M16 5.5L7 9.2v6.8c0 5.3 3.8 10.3 9 11.4 5.2-1.1 9-6.1 9-11.4V9.2L16 5.5z' fill='%231e40af' opacity='0.35'/%3E%3Cpath d='M12.5 16.5l2.5 2.5 5.5-5.5' fill='none' stroke='%23ffffff' stroke-width='2.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">
    <style>
        :root {
            color-scheme: dark;
            --pasax-bg-base: #020617;
            --pasax-card-bg: rgba(15, 23, 42, 0.9);
            --pasax-border-color: #1e293b;
            --pasax-text-main: #f8fafc;
            --pasax-text-muted: #94a3b8;
            --pasax-cyan-accent: #06b6d4;
            --pasax-cyan-glow: rgba(6, 182, 212, 0.35);
            --pasax-amber-accent: #f59e0b;
            --pasax-emerald-accent: #10b981;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: radial-gradient(1200px 600px at 50% -10%, #1e293b 0%, #0f172a 55%, var(--pasax-bg-base) 100%);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--pasax-text-main); padding: 24px;
        }
        .pasax-card {
            width: 100%; max-width: 540px; background: var(--pasax-card-bg);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--pasax-border-color); border-radius: 22px; padding: 36px 32px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.65), 0 0 1px rgba(255, 255, 255, 0.1) inset;
            text-align: center;
        }
        .pasax-badge-header {
            display: inline-flex; align-items: center; gap: 6px; font-weight: 700;
            letter-spacing: .08em; font-size: 11.5px; color: var(--pasax-amber-accent);
            border: 1px solid rgba(245, 158, 11, .35); background: rgba(245, 158, 11, .12);
            padding: 5px 14px; border-radius: 999px; text-transform: uppercase;
        }
        .pasax-title { margin: 20px 0 8px; font-size: 23px; color: var(--pasax-text-main); font-weight: 700; letter-spacing: -0.02em; }
        .pasax-desc { margin: 0 0 24px; color: var(--pasax-text-muted); font-size: 13.5px; line-height: 1.6; }

        .pasax-timer-container {
            position: relative; width: 144px; height: 144px; margin: 0 auto 20px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .pasax-timer-svg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            transform: rotate(-90deg);
        }
        .pasax-timer-circle-bg {
            fill: none; stroke: rgba(30, 41, 59, 0.7); stroke-width: 8;
        }
        .pasax-timer-circle-progress {
            fill: none; stroke: var(--pasax-cyan-accent); stroke-width: 8;
            stroke-linecap: round;
            stroke-dasharray: 402;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 1s linear, stroke 0.3s ease;
            filter: drop-shadow(0 0 8px var(--pasax-cyan-glow));
        }
        .pasax-timer-val {
            font-size: 46px; font-weight: 800; color: var(--pasax-cyan-accent);
            font-variant-numeric: tabular-nums; line-height: 1; z-index: 1;
        }
        .pasax-timer-unit {
            font-size: 10.5px; font-weight: 700; text-transform: uppercase;
            color: #64748b; margin-top: 4px; letter-spacing: .08em; z-index: 1;
        }

        .pasax-steps-bar {
            display: flex; justify-content: center; gap: 8px; margin-bottom: 22px; flex-wrap: wrap;
        }
        .pasax-step-chip {
            font-size: 11px; padding: 5px 11px; border-radius: 7px; font-weight: 600;
            background: #0b1220; border: 1px solid var(--pasax-border-color); color: #64748b;
        }
        .pasax-step-chip.active {
            background: rgba(6, 182, 212, .14); border-color: rgba(6, 182, 212, .45); color: #22d3ee;
            box-shadow: 0 0 10px rgba(6, 182, 212, 0.15);
        }

        .pasax-rows { display: grid; gap: 8px; margin: 0 0 24px; text-align: left; }
        .pasax-row {
            display: flex; justify-content: space-between; align-items: center; gap: 12px; font-size: 12.5px;
            background: #0b1220; border: 1px solid var(--pasax-border-color); border-radius: 9px; padding: 10px 14px;
        }
        .pasax-row span { color: #64748b; font-weight: 500; }
        .pasax-row b { color: #cbd5e1; font-weight: 600; word-break: break-all; }

        .pasax-btn-retry {
            width: 100%; padding: 14px 20px; border: 0; border-radius: 11px;
            background: #1e293b; color: #64748b; font-weight: 700; font-size: 14.5px;
            cursor: not-allowed; transition: all .25s ease;
        }
        .pasax-btn-retry.ready {
            background: var(--pasax-cyan-accent); color: #04212b; cursor: pointer;
            box-shadow: 0 0 25px var(--pasax-cyan-glow);
        }
        .pasax-btn-retry.ready:hover {
            background: #22d3ee; transform: translateY(-1px);
        }
        .pasax-footer-note {
            font-size: 12px; color: #64748b; margin-top: 18px; line-height: 1.5;
        }
    </style>
</head>
<body>
<main class="pasax-card">
    <div class="pasax-badge-header">⏳ {{ __('Jeda Pengamanan Login Bertingkat') }}</div>
    <h1 class="pasax-title">{{ __('Akses Login Dibatasi Sementara') }}</h1>
    <p class="pasax-desc">{{ $msg }}</p>

    <div class="pasax-timer-container">
        <svg class="pasax-timer-svg" viewBox="0 0 144 144">
            <circle class="pasax-timer-circle-bg" cx="72" cy="72" r="64"></circle>
            <circle class="pasax-timer-circle-progress" id="pasax-timer-progress" cx="72" cy="72" r="64"></circle>
        </svg>
        <div class="pasax-timer-val" id="pasax-countdown-val">{{ $sec }}</div>
        <div class="pasax-timer-unit">{{ __('Detik') }}</div>
    </div>

    <div class="pasax-steps-bar">
        <div class="pasax-step-chip @if ($level === 1) active @endif">1. {{ __('Tahap 1: 30s') }}</div>
        <div class="pasax-step-chip @if ($level === 2) active @endif">2. {{ __('Tahap 2: 60s') }}</div>
        <div class="pasax-step-chip @if ($level >= 3) active @endif">3. {{ __('Tahap 3: 120s') }}</div>
        <div class="pasax-step-chip">{{ __('Reset: :hours Jam', ['hours' => $cycleHours]) }}</div>
    </div>

    <div class="pasax-rows">
        <div class="pasax-row">
            <span>{{ __('Status Cooldown') }}</span>
            <b style="color: var(--pasax-amber-accent);" id="pasax-status-label">{{ __('Jeda Aktif (Tahap :level)', ['level' => $level]) }}</b>
        </div>
        <div class="pasax-row">
            <span>{{ __('Alamat IP') }}</span>
            <b style="font-family: monospace;">{{ $clientIp }}</b>
        </div>
        <div class="pasax-row">
            <span>{{ __('Target Akun') }}</span>
            <b>{{ $userEmail }}</b>
        </div>
        <div class="pasax-row">
            <span>{{ __('Masa Reset Siklus') }}</span>
            <b>{{ __(':hours Jam Tanpa Kegagalan', ['hours' => $cycleHours]) }}</b>
        </div>
    </div>

    <button id="pasax-retry-btn" class="pasax-btn-retry" disabled onclick="window.location.href='{{ $targetUrl }}'">
        <span id="pasax-btn-text">{{ __('Tunggu Cooldown (:sec s)...', ['sec' => $sec]) }}</span>
    </button>

    <div class="pasax-footer-note">
        {{ __('Sistem rate limiting Pasax akan membuka kembali formulir login secara otomatis setelah hitungan mundur berakhir tanpa perlu mengajukan tiket pembukaan blokir ke admin.') }}
    </div>
</main>

<script>
(function() {
    var initial = {{ $sec }};
    var current = initial;
    var perimeter = 2 * Math.PI * 64; // ~402.12
    var valEl = document.getElementById('pasax-countdown-val');
    var progressEl = document.getElementById('pasax-timer-progress');
    var btn = document.getElementById('pasax-retry-btn');
    var btnText = document.getElementById('pasax-btn-text');
    var statusLabel = document.getElementById('pasax-status-label');
    var targetUrl = @json($targetUrl);

    function tick() {
        if (current <= 0) {
            if (valEl) valEl.textContent = '0';
            if (progressEl) {
                progressEl.style.strokeDashoffset = perimeter;
                progressEl.style.stroke = '#10b981';
            }
            if (btn) {
                btn.disabled = false;
                btn.classList.add('ready');
            }
            if (btnText) btnText.textContent = '✓ ' + @json(__('Coba Login Lagi Sekarang'));
            if (statusLabel) {
                statusLabel.textContent = @json(__('Waktu Cooldown Selesai (Siap Login)'));
                statusLabel.style.color = '#10b981';
            }
            document.title = '✓ ' + @json(__('Siap Login — Pasax'));
            return;
        }

        if (valEl) valEl.textContent = current;
        if (progressEl && initial > 0) {
            var fraction = (initial - current) / initial;
            progressEl.style.strokeDashoffset = perimeter * fraction;
        }
        if (btnText) btnText.textContent = @json(__('Tunggu Cooldown')) + ' (' + current + 's)...';
        document.title = '(' + current + 's) ' + @json(__('Cooldown Login — Pasax'));

        current--;
        setTimeout(tick, 1000);
    }

    tick();
})();
</script>
</body>
</html>
