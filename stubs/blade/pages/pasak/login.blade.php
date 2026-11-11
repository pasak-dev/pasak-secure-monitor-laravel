@php
    $currentLocale = (string) (request()->query('lang')
        ?? (request()->hasSession() ? session('pasak_locale') : null)
        ?? request()->cookie('pasak_locale')
        ?? app()->getLocale()
        ?? 'en');
    if (! in_array($currentLocale, ['en', 'id'], true)) {
        $currentLocale = 'en';
    }
    app()->setLocale($currentLocale);

    $pasakTimezone = \Internal\Pasak\Support\PasakDashboardView::timezone();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Login Dashboard') }} — Pasak Autonomous Security</title>
    @php
        $customFavicon = config("pasak.dashboard.favicon");
        $defaultFavicon = "data:image/png;base64,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";
    @endphp
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

        [data-sec-theme="light"] {
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
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: var(--sec-primary-light);
            border: 1px solid rgba(59, 130, 246, 0.25);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
        }

        .sec-login-logo {
            width: 36px;
            height: 36px;
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
                    <img src="{{ $customFavicon ?: $defaultFavicon }}" alt="Pasak Logo" class="sec-login-logo">
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

            @if ($errors->has('pasak_login'))
                <div class="sec-alert sec-alert-danger">
                    <span>⚠</span>
                    <span>{{ $errors->first('pasak_login') }}</span>
                </div>
            @endif

            @if (! $secretConfigured)
                <div class="sec-alert sec-alert-warning">
                    <span>⚠</span>
                    <span>{{ __('PASAK_DASHBOARD_SECRET belum dikonfigurasi di .env. Jalankan "php artisan pasak:secret" terlebih dahulu.') }}</span>
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('pasak.dashboard.login.submit') }}">
                @csrf

                <!-- Username Input -->
                <div class="sec-form-group">
                    <label class="sec-form-label" for="pasak_username">
                        <span>{{ __('Username') }}</span>
                        <span style="font-size: 11px; color: var(--sec-text-muted); font-weight: normal;">PASAK_DASHBOARD_USERNAME</span>
                    </label>
                    <div class="sec-input-wrap">
                        <span class="sec-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input id="pasak_username" type="text" name="username" class="sec-input" value="{{ old('username', $defaultUsername) }}" required autofocus placeholder="admin" autocomplete="username">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="sec-form-group">
                    <label class="sec-form-label" for="pasak_password">
                        <span>{{ __('Secret Password') }}</span>
                        <span style="font-size: 11px; color: var(--sec-text-muted); font-weight: normal;">PASAK_DASHBOARD_SECRET</span>
                    </label>
                    <div class="sec-input-wrap">
                        <span class="sec-input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input id="pasak_password" type="password" name="password" class="sec-input sec-has-action" required placeholder="{{ __('Password dasbor (bawaan: Admin)') }}" autocomplete="current-password">
                        <button type="button" class="sec-input-btn" onclick="window.pasakTogglePassword();" title="{{ __('Lihat / Sembunyikan Password') }}">
                            <svg id="pasakEyeIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                    {{ __('Username:') }} <code>PASAK_DASHBOARD_USERNAME</code> ({{ __('bawaan') }}: <code>{{ $defaultUsername ?? 'admin' }}</code>).<br>
                    {{ __('Password awal:') }} <code>PASAK_DASHBOARD_PASSWORD</code> ({{ __('bawaan') }}: <code>{{ $defaultPassword ?? 'Admin' }}</code>) {{ __('atau kunci rahasia') }} <code>PASAK_DASHBOARD_SECRET</code>.<br>
                    <span style="opacity: 0.85;">{{ __('Setelah masuk, Anda dapat memperbarui password yang disimpan aman ke database.') }}</span><br>
                    <span style="color: var(--sec-primary); font-size: 11px;">🔒 {{ __('Lupa password? Masukkan nilai PASAK_DASHBOARD_SECRET dari berkas .env sebagai password darurat.') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Controls -->
        <div class="sec-login-footer">
            <div>
                <span>Pasak v{{ \Internal\Pasak\Services\PasakService::VERSION }}</span>
                <span> &bull; </span>
                <span>{{ \Internal\Pasak\Support\DashboardView::formatDate(now(), 'H:i T') }}</span>
            </div>
            <div class="sec-lang-switcher" title="{{ __('Pilih Bahasa / Select Language') }}">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="sec-lang-btn {{ $currentLocale === 'en' ? 'is-active' : '' }}">EN</a>
                <span style="opacity: 0.4;">/</span>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'id']) }}" class="sec-lang-btn {{ $currentLocale === 'id' ? 'is-active' : '' }}">ID</a>
            </div>
        </div>
    </div>

    <script>
        window.pasakTogglePassword = function() {
            var input = document.getElementById('pasak_password');
            var icon = document.getElementById('pasakEyeIcon');
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
