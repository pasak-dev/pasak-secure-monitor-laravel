@php
    $currentLocale = (string) (request()->query('lang')
        ?? (request()->hasSession() ? session('pasax_locale') : null)
        ?? request()->cookie('pasax_locale')
        ?? app()->getLocale()
        ?? 'en');
    if (! in_array($currentLocale, ['en', 'id'], true)) {
        $currentLocale = 'en';
    }
    app()->setLocale($currentLocale);

    $pasaxTimezone = \Pasax\SecureMonitor\Support\PasaxDashboardView::timezone();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login Dashboard') }} — Pasax Autonomous Security</title>
    @php
        $customFavicon = config("pasax.dashboard.favicon");
        $defaultFavicon = "data:image/png;base64,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";
    @endphp
        <script>
        // Terapkan tema tersimpan sebelum paint agar konsisten dengan dashboard
        (function() {
            try {
                var mode = localStorage.getItem('pasax-theme') || 'auto';
                var isDark = mode === 'dark' || (mode !== 'light' && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
                if (mode === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.documentElement.classList.add('dark');
                } else if (mode === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                    document.documentElement.classList.remove('dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            } catch (e) {}
        })();
    </script>
    <link rel="icon" href="{{ $customFavicon ?: $defaultFavicon }}">

    <style>
        :root {
            --sec-bg: #090d16;
            --sec-card: #111827;
            --sec-card-hover: #172236;
            --sec-border: #1f293d;
            --sec-primary: #3b82f6;
            --sec-primary-hover: #2563eb;
            --sec-primary-light: rgba(59, 130, 246, 0.12);
            --sec-text: #f8fafc;
            --sec-text-muted: #94a3b8;
            --sec-danger: #ef4444;
            --sec-danger-light: rgba(239, 68, 68, 0.12);
            --sec-success: #10b981;
            --sec-success-light: rgba(16, 185, 129, 0.12);
            --sec-warning: #f59e0b;
            --sec-warning-light: rgba(245, 158, 11, 0.12);
            --sec-radius: 12px;
            --sec-radius-sm: 8px;
            --sec-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.4);
            --sec-glow: 0 0 40px rgba(59, 130, 246, 0.15);
        }

        [data-sec-theme="light"],
        html[data-theme="light"] {
            --sec-bg: #f1f5f9;
            --sec-card: #ffffff;
            --sec-card-hover: #f8fafc;
            --sec-border: #e2e8f0;
            --sec-primary: #2563eb;
            --sec-primary-hover: #1d4ed8;
            --sec-primary-light: rgba(37, 99, 235, 0.08);
            --sec-text: #0f172a;
            --sec-text-muted: #64748b;
            --sec-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            --sec-glow: 0 0 40px rgba(37, 99, 235, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--sec-bg);
            color: var(--sec-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* Background cyber grid & glow effect */
        .sec-bg-grid {
            position: absolute;
            inset: 0;
            background-image: 
                radial-gradient(circle at 50% 25%, rgba(59, 130, 246, 0.12) 0%, transparent 60%),
                linear-gradient(to right, rgba(255, 255, 255, 0.02) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.02) 1px, transparent 1px);
            background-size: 100% 100%, 36px 36px, 36px 36px;
            pointer-events: none;
            z-index: 0;
        }

        .sec-login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        .sec-login-card {
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius);
            box-shadow: var(--sec-shadow), var(--sec-glow);
            padding: 34px 32px;
            backdrop-filter: blur(12px);
        }

        .sec-login-header {
            text-align: center;
            margin-bottom: 26px;
        }

        .sec-login-logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }

        .sec-login-logo-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: opacity 0.15s ease-in-out;
        }

        .sec-login-logo-link:hover {
            opacity: 0.85;
        }

        .sec-header-logo {
            height: 38px;
            width: auto;
            max-width: 170px;
            object-fit: contain;
            display: block;
        }

        /* Default: Mode Gelap (Login Card bawaan bertema dark) */
        .sec-header-logo-light {
            display: none !important;
        }

        .sec-header-logo-dark {
            display: block !important;
        }

        /* Mode Terang eksplisit */
        html[data-theme="light"] .sec-header-logo-light,
        html[data-sec-theme="light"] .sec-header-logo-light {
            display: block !important;
        }

        html[data-theme="light"] .sec-header-logo-dark,
        html[data-sec-theme="light"] .sec-header-logo-dark {
            display: none !important;
        }

        /* Mode Terang bawaan sistem jika belum ada pilihan tema eksplisit */
        @media (prefers-color-scheme: light) {
            html:not([data-theme="dark"]):not(.dark) .sec-header-logo-light {
                display: block !important;
            }

            html:not([data-theme="dark"]):not(.dark) .sec-header-logo-dark {
                display: none !important;
            }

            html:not([data-theme="dark"]):not(.dark) {
                --sec-bg: #f1f5f9;
                --sec-card: #ffffff;
                --sec-card-hover: #f8fafc;
                --sec-border: #e2e8f0;
                --sec-primary: #2563eb;
                --sec-primary-hover: #1d4ed8;
                --sec-primary-light: rgba(37, 99, 235, 0.08);
                --sec-text: #0f172a;
                --sec-text-muted: #64748b;
                --sec-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
                --sec-glow: 0 0 40px rgba(37, 99, 235, 0.08);
            }
        }

        .sec-login-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--sec-text);
            margin-bottom: 6px;
            letter-spacing: -0.02em;
        }

        .sec-login-subtitle {
            font-size: 13px;
            color: var(--sec-text-muted);
            line-height: 1.45;
        }

        .sec-alert {
            padding: 12px 14px;
            border-radius: var(--sec-radius-sm);
            font-size: 12.5px;
            line-height: 1.45;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .sec-alert-danger {
            background: var(--sec-danger-light);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .sec-alert-success {
            background: var(--sec-success-light);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .sec-alert-warning {
            background: var(--sec-warning-light);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fcd34d;
        }

        .sec-form-group {
            margin-bottom: 18px;
        }

        .sec-form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--sec-text);
            margin-bottom: 6px;
        }

        .sec-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .sec-input-icon {
            position: absolute;
            left: 12px;
            color: var(--sec-text-muted);
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .sec-input {
            width: 100%;
            height: 42px;
            padding: 0 14px 0 38px;
            font-size: 13.5px;
            background: var(--sec-bg);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            color: var(--sec-text);
            transition: all 0.15s ease;
            outline: none;
        }

        .sec-input:focus {
            border-color: var(--sec-primary);
            box-shadow: 0 0 0 3px var(--sec-primary-light);
        }

        .sec-input.sec-has-action {
            padding-right: 40px;
        }

        .sec-input-btn {
            position: absolute;
            right: 8px;
            background: none;
            border: none;
            color: var(--sec-text-muted);
            cursor: pointer;
            padding: 6px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sec-input-btn:hover {
            color: var(--sec-text);
            background: rgba(255, 255, 255, 0.05);
        }

        .sec-btn-submit {
            width: 100%;
            height: 42px;
            background: var(--sec-primary);
            color: #ffffff;
            border: none;
            border-radius: var(--sec-radius-sm);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.15s ease;
            margin-top: 6px;
        }

        .sec-btn-submit:hover {
            background: var(--sec-primary-hover);
            transform: translateY(-1px);
        }

        .sec-btn-submit:active {
            transform: translateY(0);
        }

        .sec-login-hint {
            margin-top: 22px;
            padding: 12px;
            border-radius: var(--sec-radius-sm);
            background: rgba(255, 255, 255, 0.02);
            border: 1px dashed var(--sec-border);
            font-size: 11.5px;
            color: var(--sec-text-muted);
            line-height: 1.5;
        }

        .sec-login-hint code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: rgba(59, 130, 246, 0.1);
            color: var(--sec-primary);
            padding: 1px 4px;
            border-radius: 4px;
            font-size: 11px;
        }

        .sec-login-footer {
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: var(--sec-text-muted);
            padding: 0 4px;
        }

        .sec-lang-switcher {
            display: inline-flex;
            align-items: center;
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: 6px;
            padding: 2px 4px;
            font-size: 11px;
        }

        .sec-lang-btn {
            background: none;
            border: none;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            color: var(--sec-text-muted);
            cursor: pointer;
        }

        .sec-lang-btn.is-active {
            background: var(--sec-primary);
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="sec-bg-grid" aria-hidden="true"></div>

    <div class="sec-login-container">
        <div class="sec-login-card">
            <!-- Header Brand -->
            <div class="sec-login-header">
                <div class="sec-login-logo-wrap">
                    <a href="{{ route('pasax.dashboard.login') }}" class="sec-login-logo-link" title="{{ __('Pasax Autonomous Security') }}">
                        <img class="sec-header-logo sec-header-logo-light" src="data:image/png;base64,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" alt="PASAX" width="134" height="40">
                        <img class="sec-header-logo sec-header-logo-dark" src="data:image/png;base64,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" alt="PASAX" width="134" height="40">
                    </a>
                </div>
                <h1 class="sec-login-title">{{ __('Autentikasi Dashboard') }}</h1>
                <p class="sec-login-subtitle">
                    {{ __('Masukkan kredensial yang tercantum pada berkas .env server Anda') }}
                </p>
            </div>

            <!-- Alerts -->
            @if (session('status'))
                <div class="sec-alert sec-alert-success">
                    <span>✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->has('pasax_login'))
                <div class="sec-alert sec-alert-danger">
                    <span>⚠</span>
                    <span>{{ $errors->first('pasax_login') }}</span>
                </div>
            @endif

            @if (! $secretConfigured)
                <div class="sec-alert sec-alert-warning">
                    <span>⚠</span>
                    <span>{{ __('PASAX_DASHBOARD_SECRET belum dikonfigurasi di .env. Jalankan "php artisan pasax:secret" terlebih dahulu.') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('pasax.dashboard.login.submit') }}">
                @csrf

                <!-- Username Input -->
                <div class="sec-form-group">
                    <label class="sec-form-label" for="pasax_username">
                        <span>{{ __('Username') }}</span>
                        <span style="font-size: 11px; color: var(--sec-text-muted); font-weight: normal;">PASAX_DASHBOARD_USERNAME</span>
                    </label>
                    <div class="sec-input-wrap">
                        <span class="sec-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input id="pasax_username" type="text" name="username" class="sec-input" value="{{ old('username', $defaultUsername) }}" required autofocus placeholder="admin" autocomplete="username">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="sec-form-group">
                    <label class="sec-form-label" for="pasax_password">
                        <span>{{ __('Secret Password') }}</span>
                        <span style="font-size: 11px; color: var(--sec-text-muted); font-weight: normal;">PASAX_DASHBOARD_SECRET</span>
                    </label>
                    <div class="sec-input-wrap">
                        <span class="sec-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input id="pasax_password" type="password" name="password" class="sec-input sec-has-action" required placeholder="{{ __('Password dasbor (bawaan: Admin)') }}" autocomplete="current-password">
                        <button type="button" class="sec-input-btn" onclick="window.pasaxTogglePassword();" title="{{ __('Lihat / Sembunyikan Password') }}">
                            <svg id="pasaxEyeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="sec-btn-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    <span>{{ __('Masuk ke Dashboard') }}</span>
                </button>
            </form>

            <!-- Hint Box -->
            <div class="sec-login-hint">
                <div style="font-weight: 600; margin-bottom: 3px; color: var(--sec-text);">
                    ℹ️ {{ __('Panduan Kredensial Dasbor') }}
                </div>
                <div style="line-height: 1.5;">
                    {{ __('Username:') }} <code>PASAX_DASHBOARD_USERNAME</code> ({{ __('bawaan') }}: <code>{{ $defaultUsername ?? 'admin' }}</code>).<br>
                    {{ __('Password awal:') }} <code>PASAX_DASHBOARD_PASSWORD</code> ({{ __('bawaan') }}: <code>{{ $defaultPassword ?? 'Admin' }}</code>) {{ __('atau kunci rahasia') }} <code>PASAX_DASHBOARD_SECRET</code>.<br>
                    <span style="opacity: 0.85;">{{ __('Setelah masuk, Anda dapat memperbarui password yang disimpan aman ke database.') }}</span><br>
                    <span style="color: var(--sec-primary); font-size: 11px;">🔒 {{ __('Lupa password? Masukkan nilai PASAX_DASHBOARD_SECRET dari berkas .env sebagai password darurat.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Controls -->
        <div class="sec-login-footer">
            <div>
                <span>Pasax v{{ \Pasax\SecureMonitor\Services\PasaxService::VERSION }}</span>
                <span> &bull; </span>
                <span>{{ \Pasax\SecureMonitor\Support\DashboardView::formatDate(now(), 'H:i T') }}</span>
            </div>
            <div class="sec-lang-switcher" title="{{ __('Pilih Bahasa / Select Language') }}">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="sec-lang-btn {{ $currentLocale === 'en' ? 'is-active' : '' }}">EN</a>
                <span style="opacity: 0.4;">/</span>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'id']) }}" class="sec-lang-btn {{ $currentLocale === 'id' ? 'is-active' : '' }}">ID</a>
            </div>
        </div>
    </div>

    <script>
        window.pasaxTogglePassword = function() {
            var input = document.getElementById('pasax_password');
            var icon = document.getElementById('pasaxEyeIcon');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) {
                    icon.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
                }
            } else {
                input.type = 'password';
                if (icon) {
                    icon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
                }
            }
        };
    </script>
</body>

</html>
