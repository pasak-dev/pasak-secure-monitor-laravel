@php
    $appLocale = config('app.locale', env('APP_LOCALE', 'en'));
    if (! in_array($appLocale, ['en', 'id'], true)) {
        $appLocale = 'en';
    }
    $currentLocale = (string) (request()->query('lang')
        ?? (request()->hasSession() ? session('pasax_locale') : null)
        ?? request()->cookie('pasax_locale')
        ?? $appLocale);
    if (! in_array($currentLocale, ['en', 'id'], true)) {
        $currentLocale = $appLocale;
    }
    app()->setLocale($currentLocale);
    \Illuminate\Support\Carbon::setLocale($currentLocale);
    if (class_exists(\Carbon\Carbon::class)) {
        \Carbon\Carbon::setLocale($currentLocale);
    }
    $pasaxTimezone = \Pasax\SecureMonitor\Support\DashboardView::timezone();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($heading ?? 'Dashboard') }} — Pasax</title>
    @php
        $customFavicon = config("pasax.dashboard.favicon");
        $defaultFavicon = "data:image/png;base64,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";
        $faviconUrl = $customFavicon ?: $defaultFavicon;
    @endphp
    @hasSection("favicon")
        @yield("favicon")
    @else
        <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="image/png" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @endif
    <script>
        // Terapkan tema tersimpan sebelum paint agar tidak flicker.
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
            } catch (e) {
                /* abaikan: tetap ikuti sistem */ }
        })();
    </script>
    <style>
        :root,
        html,
        body.sec-body,
        .sec-root {
            --sec-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --sec-font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            --sec-bg: #f8fafc;
            --sec-card: #ffffff;
            --sec-card-hover: #f1f5f9;
            --sec-border: #e2e8f0;
            --sec-border-light: #f1f5f9;
            --sec-text: #0f172a;
            --sec-text-muted: #64748b;
            --sec-text-subtle: #94a3b8;
            --sec-primary: #2563eb;
            --sec-primary-hover: #1d4ed8;
            --sec-primary-fg: #ffffff;
            --sec-danger: #ef4444;
            --sec-danger-bg: #fef2f2;
            --sec-danger-border: #fecaca;
            --sec-danger-hover: #dc2626;
            --sec-warning: #f59e0b;
            --sec-warning-bg: #fffbeb;
            --sec-warning-border: #fde68a;
            --sec-success: #10b981;
            --sec-success-bg: #ecfdf5;
            --sec-success-border: #a7f3d0;
            --sec-radius: 10px;
            --sec-radius-sm: 6px;
            --sec-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.07), 0 1px 2px -1px rgba(0, 0, 0, 0.05);
            --sec-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            color-scheme: light;
        }

        body.sec-body {
            background: var(--sec-bg);
            color: var(--sec-text);
            font-family: var(--sec-font);
            margin: 0;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .sec-root {
            font-family: var(--sec-font);
            color: var(--sec-text);
            width: 100%;
            box-sizing: border-box;
        }

        /* Mode Gelap Otomatis dari Sistem OS (hanya berlaku jika pengguna memilih 'auto' / tanpa data-theme="light") */
        @media (prefers-color-scheme: dark) {

            :root:not([data-theme="light"]),
            html:not([data-theme="light"]),
            html:not([data-theme="light"]) body.sec-body,
            html:not([data-theme="light"]) .sec-root {
                color-scheme: dark;
                --sec-bg: #090d16;
                --sec-card: #111827;
                --sec-card-hover: #1e293b;
                --sec-border: #1e293b;
                --sec-border-light: #162032;
                --sec-text: #f9fafb;
                --sec-text-muted: #9ca3af;
                --sec-text-subtle: #6b7280;
                --sec-primary: #3b82f6;
                --sec-primary-hover: #2563eb;
                --sec-danger: #f87171;
                --sec-danger-bg: rgba(239, 68, 68, 0.15);
                --sec-danger-border: rgba(239, 68, 68, 0.3);
                --sec-warning: #fbbf24;
                --sec-warning-bg: rgba(245, 158, 11, 0.15);
                --sec-warning-border: rgba(245, 158, 11, 0.3);
                --sec-success: #34d399;
                --sec-success-bg: rgba(16, 185, 129, 0.15);
                --sec-success-border: rgba(16, 185, 129, 0.3);
                --sec-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.3);
            }
        }

        /* Override Mode Terang Eksplisit */
        html[data-theme="light"],
        html[data-theme="light"] body.sec-body,
        html[data-theme="light"] .sec-root,
        [data-theme="light"] {
            color-scheme: light !important;
            --sec-bg: #f8fafc !important;
            --sec-card: #ffffff !important;
            --sec-card-hover: #f1f5f9 !important;
            --sec-border: #e2e8f0 !important;
            --sec-border-light: #f1f5f9 !important;
            --sec-text: #0f172a !important;
            --sec-text-muted: #64748b !important;
            --sec-text-subtle: #94a3b8 !important;
            --sec-primary: #2563eb !important;
            --sec-primary-hover: #1d4ed8 !important;
            --sec-primary-fg: #ffffff !important;
            --sec-danger: #ef4444 !important;
            --sec-danger-bg: #fef2f2 !important;
            --sec-danger-border: #fecaca !important;
            --sec-danger-hover: #dc2626 !important;
            --sec-warning: #f59e0b !important;
            --sec-warning-bg: #fffbeb !important;
            --sec-warning-border: #fde68a !important;
            --sec-success: #10b981 !important;
            --sec-success-bg: #ecfdf5 !important;
            --sec-success-border: #a7f3d0 !important;
            --sec-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.07), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
            --sec-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -2px rgba(0, 0, 0, 0.05) !important;
        }

        /* Override Mode Gelap Eksplisit */
        html[data-theme="dark"],
        html[data-theme="dark"] body.sec-body,
        html[data-theme="dark"] .sec-root,
        html.dark,
        html.dark body.sec-body,
        html.dark .sec-root,
        [data-theme="dark"] {
            color-scheme: dark !important;
            --sec-bg: #090d16 !important;
            --sec-card: #111827 !important;
            --sec-card-hover: #1e293b !important;
            --sec-border: #1e293b !important;
            --sec-border-light: #162032 !important;
            --sec-text: #f9fafb !important;
            --sec-text-muted: #9ca3af !important;
            --sec-text-subtle: #6b7280 !important;
            --sec-primary: #3b82f6 !important;
            --sec-primary-hover: #2563eb !important;
            --sec-danger: #f87171 !important;
            --sec-danger-bg: rgba(239, 68, 68, 0.15) !important;
            --sec-danger-border: rgba(239, 68, 68, 0.3) !important;
            --sec-warning: #fbbf24 !important;
            --sec-warning-bg: rgba(245, 158, 11, 0.15) !important;
            --sec-warning-border: rgba(245, 158, 11, 0.3) !important;
            --sec-success: #34d399 !important;
            --sec-success-bg: rgba(16, 185, 129, 0.15) !important;
            --sec-success-border: rgba(16, 185, 129, 0.3) !important;
            --sec-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.3) !important;
        }

        .sec-root * {
            box-sizing: border-box;
        }

        /* Layout Structure */
        .sec-container {
            display: flex;
            gap: 28px;
            align-items: flex-start;
            width: 100%;
        }

        .sec-sidebar {
            width: 210px;
            flex-shrink: 0;
            position: sticky;
            top: 20px;
        }

        .sec-main {
            flex: 1;
            min-width: 0;
            width: 100%;
        }

        /* Navigation */
        .sec-nav-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sec-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            font-size: 14px;
            font-weight: 500;
            color: var(--sec-text-muted);
            text-decoration: none;
            border-radius: var(--sec-radius-sm);
            transition: all 0.15s ease-in-out;
        }

        .sec-nav-item:hover {
            color: var(--sec-text);
            background: var(--sec-card-hover);
        }

        .sec-nav-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .sec-nav-item.active {
            color: var(--sec-primary);
            background: rgba(37, 99, 235, 0.08);
            font-weight: 600;
        }

        .sec-nav-badge {
            margin-left: auto;
            background: var(--sec-danger);
            color: #fff;
            font-size: 11px;
            padding: 1px 6px;
            border-radius: 9999px;
            font-weight: 600;
        }

        /* Header (Sticky with Global Refresh Action) */
        .sec-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--sec-bg);
            padding: 12px 0;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            border-bottom: 1px solid var(--sec-border);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }

        .sec-header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
            flex: 1;
        }

        .sec-header-logo-link {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            text-decoration: none;
            transition: opacity 0.15s ease-in-out;
        }

        .sec-header-logo-link:hover {
            opacity: 0.85;
        }

        .sec-header-logo {
            height: 28px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .sec-header-logo-dark {
            display: none !important;
        }

        .sec-header-logo-light {
            display: block !important;
        }

        html[data-theme="dark"] .sec-header-logo-light,
        html.dark .sec-header-logo-light {
            display: none !important;
        }

        html[data-theme="dark"] .sec-header-logo-dark,
        html.dark .sec-header-logo-dark {
            display: block !important;
        }

        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) .sec-header-logo-light,
            html:not([data-theme="light"]) .sec-header-logo-light {
                display: none !important;
            }
            :root:not([data-theme="light"]) .sec-header-logo-dark,
            html:not([data-theme="light"]) .sec-header-logo-dark {
                display: block !important;
            }
        }

        .sec-header-divider {
            width: 1px;
            height: 26px;
            background: var(--sec-border);
            flex-shrink: 0;
        }

        .sec-header-titles {
            min-width: 0;
            flex: 1;
        }

        .sec-page-header {
            margin-bottom: 22px;
        }

        .sec-title {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 6px 0;
            color: var(--sec-text);
            letter-spacing: -0.02em;
            line-height: 1.25;
        }

        .sec-subtitle {
            font-size: 13.5px;
            color: var(--sec-text-muted);
            margin: 0;
            line-height: 1.5;
        }

        .sec-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
            margin-left: auto;
        }

        .sec-client-ip-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            height: 36px;
            padding: 0 12px;
            border-radius: var(--sec-radius-sm);
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            color: var(--sec-text);
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }

        .sec-client-ip-badge:hover {
            background: var(--sec-card-hover);
            border-color: var(--sec-primary);
            color: var(--sec-primary);
        }

        .sec-client-ip-badge:active {
            transform: scale(0.97);
        }

        .sec-ip-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--sec-success);
            box-shadow: 0 0 0 2px var(--sec-success-bg);
            flex-shrink: 0;
        }

        .sec-ip-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--sec-text-subtle);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .sec-ip-address {
            font-family: var(--sec-font-mono);
            font-size: 13px;
            font-weight: 700;
            color: var(--sec-text);
            letter-spacing: -0.01em;
        }

        @media (max-width: 640px) {
            .sec-ip-label {
                display: none;
            }
        }

        .sec-refresh-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            height: 36px;
            padding: 0 14px;
            border-radius: var(--sec-radius-sm);
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            color: var(--sec-text);
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }

        .sec-refresh-btn:hover {
            background: var(--sec-card-hover);
            border-color: var(--sec-primary);
            color: var(--sec-primary);
        }

        .sec-refresh-btn:active {
            transform: scale(0.97);
        }

        .sec-refresh-btn.is-refreshing .sec-refresh-icon {
            animation: sec-spin 0.8s linear infinite;
        }

        .sec-lang-switcher {
            display: inline-flex;
            align-items: center;
            height: 36px;
            padding: 2px 3px;
            border-radius: var(--sec-radius-sm);
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            user-select: none;
            gap: 2px;
        }

        .sec-lang-btn {
            background: transparent;
            border: none;
            color: var(--sec-text-muted);
            font-size: 12px;
            font-weight: 700;
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            line-height: 1;
        }

        .sec-lang-btn:hover {
            color: var(--sec-text);
            background: var(--sec-card-hover);
        }

        .sec-lang-btn.is-active {
            color: #ffffff;
            background: var(--sec-primary);
        }

        .sec-lang-sep {
            color: var(--sec-border);
            font-size: 11px;
            font-weight: 500;
        }

        .sec-support-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            height: 36px;
            padding: 0 13px;
            border-radius: var(--sec-radius-sm);
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            color: var(--sec-text-muted);
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }

        .sec-support-btn:hover {
            background: var(--sec-card-hover);
            border-color: var(--sec-primary);
            color: var(--sec-primary);
        }

        .sec-support-btn:active {
            transform: scale(0.97);
        }

        .sec-support-modal-box {
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius);
            box-shadow: var(--sec-shadow-md);
            max-width: 780px;
            width: 100%;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            animation: sec-modal-in 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sec-support-modal-body {
            padding: 22px 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 18px;
            font-size: 13.5px;
            line-height: 1.6;
            color: var(--sec-text);
        }

        .sec-support-section {
            background: var(--sec-bg);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            padding: 14px 16px;
        }

        .sec-support-section-title {
            font-size: 12.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sec-text-muted);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sec-support-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }

        .sec-support-item {
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .sec-support-item-title {
            font-weight: 600;
            color: var(--sec-text);
            margin-bottom: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .sec-support-item-desc {
            font-size: 12px;
            color: var(--sec-text-muted);
            line-height: 1.45;
        }

        .sec-back-app-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
            font-weight: 600;
            height: 36px;
            padding: 0 13px;
            border-radius: var(--sec-radius-sm);
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            color: var(--sec-text-muted);
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }

        .sec-back-app-btn:hover {
            background: var(--sec-card-hover);
            border-color: var(--sec-primary);
            color: var(--sec-primary);
        }

        .sec-back-app-btn:active {
            transform: scale(0.97);
        }

        .sec-sidebar-brand {
            margin-bottom: 12px;
            padding: 2px 4px;
            display: flex;
            align-items: center;
        }

        .sec-sidebar-logo-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            transition: opacity 0.15s ease-in-out;
        }

        .sec-sidebar-logo-link:hover {
            opacity: 0.85;
        }

        .sec-sidebar-back-link {
            margin-top: 0;
            margin-bottom: 12px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--sec-text-muted);
            background: var(--sec-card);
            border: 1px dashed var(--sec-border);
            border-radius: var(--sec-radius-sm);
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }

        .sec-sidebar-back-link:hover {
            color: var(--sec-primary);
            background: var(--sec-card-hover);
            border-color: var(--sec-primary);
            border-style: solid;
        }

        @keyframes sec-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Cards */
        .sec-card {
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius);
            padding: 20px;
            box-shadow: var(--sec-shadow);
            margin-bottom: 20px;
        }

        .sec-card-flush {
            padding: 0;
            overflow: hidden;
        }

        .sec-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--sec-border);
            flex-wrap: wrap;
            gap: 12px;
        }

        .sec-card-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
            color: var(--sec-text);
        }

        .sec-card-subtitle {
            font-size: 13px;
            color: var(--sec-text-muted);
            margin: 2px 0 0 0;
        }

        .sec-card-body {
            padding: 20px;
        }

        .sec-card-desc {
            font-size: 13px;
            color: var(--sec-text-muted);
            margin: 0 0 14px 0;
            line-height: 1.55;
        }

        .sec-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 13px;
            color: var(--sec-text);
        }

        .sec-content>*:last-child {
            margin-bottom: 0;
        }

        /* Grids */
        .sec-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .sec-grid-2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .sec-stat-card {
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius);
            padding: 18px 20px;
            box-shadow: var(--sec-shadow);
        }

        .sec-stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sec-text-muted);
            margin-bottom: 6px;
        }

        .sec-stat-value {
            font-size: 26px;
            font-weight: 700;
            color: var(--sec-text);
            line-height: 1.1;
            margin-bottom: 6px;
            font-variant-numeric: tabular-nums;
        }

        .sec-stat-value.sec-text-danger {
            color: var(--sec-danger);
        }

        .sec-stat-desc {
            font-size: 12px;
            color: var(--sec-text-subtle);
        }

        /* Toolbar & Controls */
        .sec-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .sec-toolbar-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .sec-input,
        .sec-select {
            height: 38px;
            padding: 0 12px;
            font-size: 14px;
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            color: var(--sec-text);
            font-family: inherit;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .sec-input:focus,
        .sec-select:focus,
        .sec-textarea:focus {
            outline: none;
            border-color: var(--sec-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .sec-textarea {
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            color: var(--sec-text);
            font-family: inherit;
        }

        /* Buttons */
        .sec-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 38px;
            padding: 0 16px;
            font-size: 14px;
            font-weight: 500;
            border-radius: var(--sec-radius-sm);
            cursor: pointer;
            border: 1px solid transparent;
            font-family: inherit;
            transition: all 0.15s ease-in-out;
            text-decoration: none;
            white-space: nowrap;
        }

        .sec-btn-sm {
            height: 30px;
            padding: 0 10px;
            font-size: 12px;
        }

        .sec-btn-primary {
            background: var(--sec-primary);
            color: #fff;
        }

        .sec-btn-primary:hover {
            background: var(--sec-primary-hover);
        }

        .sec-btn-danger {
            background: var(--sec-danger);
            color: #fff;
        }

        .sec-btn-danger:hover {
            background: var(--sec-danger-hover);
        }

        .sec-btn-secondary {
            background: var(--sec-card);
            border-color: var(--sec-border);
            color: var(--sec-text);
        }

        .sec-btn-secondary:hover {
            background: var(--sec-card-hover);
        }

        .sec-btn-ghost {
            background: transparent;
            color: var(--sec-text-muted);
            border: none;
            padding: 4px 8px;
            height: auto;
        }

        .sec-btn-ghost:hover {
            color: var(--sec-text);
            background: var(--sec-card-hover);
        }

        /* Badges */
        .sec-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .sec-badge-critical,
        .sec-badge-red {
            background: var(--sec-danger-bg);
            color: var(--sec-danger);
            border: 1px solid var(--sec-danger-border);
        }

        .sec-badge-high,
        .sec-badge-orange,
        .sec-badge-amber,
        .sec-badge-warning {
            background: var(--sec-warning-bg);
            color: var(--sec-warning);
            border: 1px solid var(--sec-warning-border);
        }

        .sec-badge-medium {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.25);
        }

        .sec-badge-low,
        .sec-badge-zinc {
            background: var(--sec-card-hover);
            color: var(--sec-text-muted);
            border: 1px solid var(--sec-border);
        }

        .sec-badge-success,
        .sec-badge-green {
            background: var(--sec-success-bg);
            color: var(--sec-success);
            border: 1px solid var(--sec-success-border);
        }

        /* Table */
        .sec-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .sec-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            text-align: left;
        }

        .sec-table th {
            padding: 12px 16px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sec-text-muted);
            background: var(--sec-card-hover);
            border-bottom: 1px solid var(--sec-border);
            white-space: nowrap;
        }

        .sec-table td {
            padding: 12px 16px;
            border-bottom: 1px solid var(--sec-border-light);
            color: var(--sec-text);
            vertical-align: middle;
        }

        .sec-table tr:last-child td {
            border-bottom: none;
        }

        .sec-table tr:hover td {
            background: var(--sec-card-hover);
        }

        /* Data Table & Pagination */
        .sec-datatable-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-top: 1px solid var(--sec-border);
            font-size: 13px;
            color: var(--sec-text-muted);
            flex-wrap: wrap;
            gap: 12px;
        }

        .sec-pagination {
            display: flex;
            align-items: center;
            gap: 4px;
            list-style: none;
            padding: 0;
            margin: 0;
            flex-wrap: wrap;
        }

        .sec-page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--sec-radius-sm);
            border: 1px solid var(--sec-border);
            background: var(--sec-card);
            color: var(--sec-text);
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            user-select: none;
        }

        .sec-page-btn:hover:not(.disabled) {
            background: var(--sec-card-hover);
            border-color: var(--sec-border-light);
            color: var(--sec-text);
        }

        .sec-page-btn.active {
            background: var(--sec-primary);
            border-color: var(--sec-primary);
            color: #fff;
            font-weight: 600;
        }

        .sec-page-btn.disabled {
            opacity: 0.35;
            cursor: not-allowed;
            pointer-events: none;
        }

        .sec-page-ellipsis {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 32px;
            color: var(--sec-text-muted);
            font-size: 13px;
        }

        .sec-font-mono {
            font-family: var(--sec-font-mono);
        }

        /* Floating Toast Notifications */
        .sec-toast-stack {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 100000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 420px;
            width: calc(100vw - 48px);
            pointer-events: none;
        }

        .sec-toast {
            pointer-events: auto;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border-radius: var(--sec-radius);
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            color: var(--sec-text);
            font-size: 13.5px;
            line-height: 1.45;
            animation: sec-toast-slide 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .sec-toast.sec-toast-hiding {
            opacity: 0;
            transform: translateX(30px);
        }

        @keyframes sec-toast-slide {
            from {
                opacity: 0;
                transform: translateY(-16px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .sec-toast-icon {
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
        }

        .sec-toast-success .sec-toast-icon {
            background: var(--sec-success-bg);
            color: var(--sec-success);
            border: 1px solid var(--sec-success-border);
        }

        .sec-toast-error .sec-toast-icon,
        .sec-toast-danger .sec-toast-icon {
            background: var(--sec-danger-bg);
            color: var(--sec-danger);
            border: 1px solid var(--sec-danger-border);
        }

        .sec-toast-warning .sec-toast-icon {
            background: var(--sec-warning-bg);
            color: var(--sec-warning);
            border: 1px solid var(--sec-warning-border);
        }

        .sec-toast-info .sec-toast-icon {
            background: rgba(37, 99, 235, 0.1);
            color: var(--sec-primary);
            border: 1px solid rgba(37, 99, 235, 0.25);
        }

        .sec-toast-content {
            flex: 1;
            min-width: 0;
        }

        .sec-toast-title {
            font-weight: 700;
            font-size: 13.5px;
            margin-bottom: 2px;
            color: var(--sec-text);
        }

        .sec-toast-message {
            font-size: 13px;
            color: var(--sec-text-muted);
            word-break: break-word;
        }

        .sec-toast-close {
            background: transparent;
            border: none;
            color: var(--sec-text-subtle);
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            padding: 2px 4px;
            margin: -2px -4px 0 0;
            border-radius: 4px;
            transition: color 0.15s;
        }

        .sec-toast-close:hover {
            color: var(--sec-text);
        }

        /* Notification / Toast */
        .sec-alert {
            padding: 12px 16px;
            border-radius: var(--sec-radius-sm);
            margin-bottom: 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sec-alert-success {
            background: var(--sec-success-bg);
            border: 1px solid var(--sec-success-border);
            color: var(--sec-success);
        }

        .sec-alert-danger {
            background: var(--sec-danger-bg);
            border: 1px solid var(--sec-danger-border);
            color: var(--sec-danger);
        }

        /* Trend Chart Bars */
        .sec-chart-box {
            display: flex;
            align-items: flex-end;
            gap: 6px;
            height: 150px;
            padding-top: 10px;
            width: 100%;
        }

        .sec-chart-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            gap: 6px;
            position: relative;
        }

        .sec-chart-bar {
            width: 100%;
            border-radius: 4px 4px 0 0;
            background: rgba(239, 68, 68, 0.7);
            transition: all 0.2s;
            min-height: 2px;
        }

        .sec-chart-bar:hover {
            background: var(--sec-danger);
            transform: scaleY(1.02);
        }

        .sec-chart-label {
            font-size: 10px;
            color: var(--sec-text-subtle);
            white-space: nowrap;
        }

        /* Modal Styles */
        .sec-modal-backdrop[hidden] {
            display: none !important;
        }

        .sec-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 16px;
        }

        .sec-modal-box {
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius);
            box-shadow: var(--sec-shadow-md);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            animation: sec-pop 0.15s ease-out;
        }

        @keyframes sec-pop {
            from {
                opacity: 0;
                transform: scale(0.96);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .sec-modal-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--sec-border);
        }

        .sec-modal-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .sec-modal-footer {
            padding: 14px 20px;
            border-top: 1px solid var(--sec-border);
            background: var(--sec-card-hover);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }


        /* Scope Selector (Settings) */
        .sec-scope-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .sec-scope-box {
            cursor: pointer;
            border: 2px solid var(--sec-border);
            border-radius: var(--sec-radius);
            padding: 18px;
            background: var(--sec-card);
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: all 0.2s ease-in-out;
        }

        .sec-scope-box:hover {
            border-color: var(--sec-border-light);
            background: var(--sec-card-hover);
        }

        .sec-scope-box.active-primary {
            border-color: var(--sec-primary);
            background: rgba(59, 130, 246, 0.08);
        }

        .sec-scope-box.active-warning {
            border-color: var(--sec-warning);
            background: rgba(245, 158, 11, 0.08);
        }

        .sec-scope-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sec-scope-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sec-scope-title {
            font-weight: 700;
            font-size: 15px;
            color: var(--sec-text);
        }

        .sec-scope-desc {
            font-size: 13px;
            color: var(--sec-text-muted);
            line-height: 1.5;
            margin: 0;
        }

        .sec-param-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--sec-border);
        }

        /* Settings — scope pills, level cards, toggle switch */
        .sec-scope-box.active-info {
            border-color: var(--sec-primary);
            background: rgba(59, 130, 246, 0.08);
        }

        .sec-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            height: 22px;
            padding: 0 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .sec-pill-info {
            background: var(--sec-success-bg);
            color: #047857;
            border: 1px solid var(--sec-success-border);
        }

        .sec-switch-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .sec-switch {
            position: relative;
            width: 46px;
            height: 26px;
            flex: 0 0 46px;
        }

        .sec-switch input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            cursor: pointer;
            z-index: 2;
        }

        .sec-switch-track {
            position: absolute;
            inset: 0;
            border-radius: 999px;
            background: var(--sec-border);
            transition: background 0.2s ease-in-out;
        }

        .sec-switch-track::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
            transition: transform 0.2s ease-in-out;
        }

        .sec-switch input:checked~.sec-switch-track {
            background: var(--sec-success);
        }

        .sec-switch input:checked~.sec-switch-track::after {
            transform: translateX(20px);
        }

        .sec-switch-label {
            font-size: 13px;
            font-weight: 700;
            color: var(--sec-success);
        }

        .sec-switch-label.off {
            color: var(--sec-text-muted);
        }

        .sec-level-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 14px;
        }

        .sec-level-card {
            border: 1px solid var(--sec-border);
            border-left-width: 4px;
            border-left-style: solid;
            border-radius: var(--sec-radius);
            padding: 14px 16px;
            background: var(--sec-card);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .sec-level-card.critical {
            border-left-color: var(--sec-danger);
        }

        .sec-level-card.high {
            border-left-color: var(--sec-warning);
        }

        .sec-level-card.medium {
            border-left-color: var(--sec-warning);
        }

        .sec-level-card.low {
            border-left-color: var(--sec-primary);
        }

        .sec-level-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .sec-level-title {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-weight: 700;
            font-size: 14px;
            color: var(--sec-text);
        }

        .sec-level-badge {
            font-size: 10.5px;
            font-weight: 800;
            padding: 3px 8px;
            border-radius: var(--sec-radius-sm);
            white-space: nowrap;
        }

        .sec-level-badge.critical {
            background: var(--sec-danger-bg);
            color: #b91c1c;
        }

        .sec-level-badge.high {
            background: var(--sec-warning-bg);
            color: #92400e;
        }

        .sec-level-badge.medium {
            background: var(--sec-warning-bg);
            color: #92400e;
        }

        .sec-level-badge.low {
            background: var(--sec-primary-bg, #eff6ff);
            color: #1d4ed8;
        }

        .sec-level-input {
            width: 100%;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 700;
            font-family: var(--sec-font-mono);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            background: var(--sec-card-hover);
            color: var(--sec-text);
        }

        .sec-level-input:focus {
            outline: none;
            border-color: var(--sec-primary);
        }

        .sec-level-desc {
            font-size: 11.5px;
            line-height: 1.5;
            color: var(--sec-text-muted);
            margin: 0;
        }

        .sec-level-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius);
            background: var(--sec-card);
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }

        .sec-level-check:hover {
            border-color: var(--sec-border-light);
            background: var(--sec-card-hover);
        }

        .sec-level-check.checked {
            border-color: var(--sec-primary);
            background: rgba(59, 130, 246, 0.06);
        }

        .sec-level-check input {
            margin-top: 2px;
            accent-color: var(--sec-primary);
        }

        .sec-select-input {
            width: 100%;
            padding: 9px 12px;
            font-size: 13.5px;
            font-weight: 600;
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            background: var(--sec-card-hover);
            color: var(--sec-text);
        }

        .sec-select-input:focus {
            outline: none;
            border-color: var(--sec-primary);
        }

        .sec-sim-box {
            border: 1px solid var(--sec-warning-border);
            background: var(--sec-warning-bg);
            border-radius: var(--sec-radius);
            padding: 14px 16px;
            color: var(--sec-warning);
        }

        .sec-sim-chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: var(--sec-radius-sm);
            font-family: var(--sec-font-mono);
            font-size: 11.5px;
            font-weight: 700;
            color: #ffffff;
        }

        .sec-sim-chip.critical {
            background: #dc2626;
        }

        .sec-sim-chip.high {
            background: #d97706;
        }

        .sec-sim-chip.medium {
            background: #ca8a04;
        }

        .sec-sim-chip.low {
            background: #2563eb;
        }

        .sec-sim-chip.combined {
            background: #7c3aed;
        }

        /* Theme Toggle */
        .sec-theme-toggle {
            margin-top: 16px;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 36px;
            padding: 0 12px;
            font-size: 13px;
            font-weight: 500;
            border-radius: var(--sec-radius-sm);
            cursor: pointer;
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            color: var(--sec-text-muted);
            font-family: inherit;
            transition: all 0.15s ease-in-out;
        }

        .sec-theme-toggle:hover {
            color: var(--sec-text);
            background: var(--sec-card-hover);
        }

        .sec-theme-toggle svg {
            width: 16px;
            height: 16px;
        }

        .sec-sidebar-version {
            margin-top: 10px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--sec-card);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            text-decoration: none;
            transition: all 0.15s ease-in-out;
        }

        .sec-sidebar-version:hover {
            background: var(--sec-card-hover);
            border-color: var(--sec-primary);
        }

        .sec-sidebar-version-brand {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 600;
            color: var(--sec-text);
            letter-spacing: -0.01em;
        }

        .sec-sidebar-version-icon {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            flex-shrink: 0;
            display: inline-block;
        }

        .sec-sidebar-version-badge {
            font-family: var(--sec-font-mono);
            font-size: 11px;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 9999px;
            background: rgba(37, 99, 235, 0.08);
            color: var(--sec-primary);
            border: 1px solid rgba(37, 99, 235, 0.2);
            line-height: 1.2;
            transition: all 0.15s ease-in-out;
        }

        .sec-sidebar-version:hover .sec-sidebar-version-badge {
            background: var(--sec-primary);
            color: #ffffff;
        }

        /* Detail Pages */
        .sec-detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px 20px;
        }

        .sec-detail-item {
            min-width: 0;
        }

        .sec-detail-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sec-text-subtle);
            margin-bottom: 4px;
        }

        .sec-detail-value {
            font-size: 14px;
            color: var(--sec-text);
            word-break: break-word;
        }

        .sec-code-block {
            font-family: var(--sec-font-mono);
            font-size: 12.5px;
            line-height: 1.6;
            background: var(--sec-card-hover);
            border: 1px solid var(--sec-border);
            border-radius: var(--sec-radius-sm);
            padding: 14px 16px;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            max-height: 320px;
            overflow: auto;
            color: var(--sec-text);
        }

        .sec-back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--sec-text-muted);
            text-decoration: none;
            margin-bottom: 16px;
        }

        .sec-back-link:hover {
            color: var(--sec-primary);
        }

        /* Responsive Breakpoints */
        @media (max-width: 900px) {
            .sec-container {
                flex-direction: column;
                gap: 16px;
            }

            .sec-sidebar {
                width: 100%;
                position: static;
                border-bottom: 1px solid var(--sec-border);
                padding-bottom: 12px;
            }

            .sec-nav-list {
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 4px;
            }

            .sec-nav-item {
                white-space: nowrap;
            }

            .sec-grid-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .sec-grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .sec-grid-4 {
                grid-template-columns: 1fr;
            }

            .sec-toolbar {
                flex-direction: column;
                align-items: stretch;
            }

            .sec-toolbar-group {
                flex-direction: column;
                align-items: stretch;
            }

            .sec-input,
            .sec-select {
                width: 100%;
            }
        }
    </style>
</head>

<body class="sec-body" style="margin: 0;">
    @php
        $backUrl = app(\Pasax\SecureMonitor\Services\PasaxService::class)->getDashboardBackUrl();
    @endphp
    <!-- Floating Toast Notifications -->
    <div class="sec-toast-stack" id="pasaxToastStack" aria-live="polite" aria-atomic="true"></div>

    <!-- Alert-Dialog Confirm Modal -->
    <div class="sec-modal-backdrop" id="pasaxConfirmModal" hidden>
        <div class="sec-modal-box" role="dialog" aria-modal="true" aria-labelledby="pasaxConfirmTitle" style="max-width: 460px;">
            <div class="sec-modal-header" style="display: flex; align-items: center; gap: 12px; padding: 16px 20px;">
                <div id="pasaxConfirmIcon" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;"></div>
                <div style="min-width: 0; flex: 1;">
                    <h3 class="sec-card-title" id="pasaxConfirmTitle" style="font-size: 16px; margin: 0;">{{ __('Konfirmasi Tindakan') }}</h3>
                </div>
            </div>
            <div class="sec-modal-body" style="padding: 18px 20px; font-size: 13.5px; color: var(--sec-text); line-height: 1.55;">
                <p id="pasaxConfirmMessage" style="margin: 0;"></p>
            </div>
            <div class="sec-modal-footer" style="padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px; background: var(--sec-card-hover);">
                <button type="button" class="sec-btn sec-btn-secondary" id="pasaxConfirmCancel">{{ __('Batal') }}</button>
                <button type="button" class="sec-btn" id="pasaxConfirmOk">{{ __('Lanjutkan') }}</button>
            </div>
        </div>
    </div>
    <div class="sec-root">
        <div class="sec-container" style="max-width: 1600px; margin: 0 auto; padding: 24px 20px;">
            <!-- Sidebar Navigation -->
            <aside class="sec-sidebar" aria-label="Security Navigation">
            <div class="sec-sidebar-brand">
                <a href="{{ route('pasax.dashboard.overview') }}" class="sec-sidebar-logo-link" title="{{ __('Kembali ke Ringkasan Dashboard Pasax') }}">
                            <img class="sec-header-logo sec-header-logo-light" src="data:image/png;base64,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" alt="PASAX" width="94" height="28">
                            <img class="sec-header-logo sec-header-logo-dark" src="data:image/png;base64,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" alt="PASAX" width="94" height="28">
                        </a>
            </div>
            <a href="{{ $backUrl }}" class="sec-sidebar-back-link" title="{{ __('Kembali ke Dashboard Aplikasi Utama (:url)', ['url' => $backUrl]) }}">
                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"/>
                    <path d="M9 12h12"/>
                </svg>
                <span>{{ __('Dashboard Utama') }}</span>
            </a>
                <nav>
                    <ul class="sec-nav-list">
                        <li>
                            <a href="{{ route('pasax.dashboard.overview') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.overview*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="7" height="7" x="3" y="3" rx="1" />
                                    <rect width="7" height="7" x="14" y="3" rx="1" />
                                    <rect width="7" height="7" x="14" y="14" rx="1" />
                                    <rect width="7" height="7" x="3" y="14" rx="1" />
                                </svg>
                                <span>{{ __('Overview') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pasax.dashboard.logs') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.logs*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <path d="M14 2v6h6" />
                                    <path d="M9 13h6" />
                                    <path d="M9 17h6" />
                                </svg>
                                <span>{{ __('Security Logs') }}</span>
                            </a>
                        </li>
                        @if (\Route::has('pasax.dashboard.threat-map'))
                        <li>
                            <a href="{{ route('pasax.dashboard.threat-map') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.threat-map*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M2 12h20" />
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                </svg>
                                <span>{{ __('Threat Map') }}</span>
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('pasax.dashboard.blocked-ips') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.blocked-ips*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="m4.9 4.9 14.2 14.2" />
                                </svg>
                                <span>{{ __('Blocked IPs') }}</span>
                            </a>
                        </li>
                        @if (\Route::has('pasax.dashboard.allowed-ips'))
                        <li>
                            <a href="{{ route('pasax.dashboard.allowed-ips') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.allowed-ips') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                <span>{{ __('Allowed IPs') }}</span>
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('pasax.dashboard.server') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.server*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="8" x="2" y="2" rx="2" />
                                    <rect width="20" height="8" x="2" y="14" rx="2" />
                                    <path d="M6 6h.01" />
                                    <path d="M6 18h.01" />
                                </svg>
                                <span>{{ __('Server Audit') }}</span>
                            </a>
                        </li>
                        @if (\Route::has('pasax.dashboard.reports'))
                        <li>
                            <a href="{{ route('pasax.dashboard.reports') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.reports*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <path d="M14 2v6h6" />
                                    <path d="M16 13H8" />
                                    <path d="M16 17H8" />
                                    <path d="M10 9H8" />
                                </svg>
                                <span>{{ __('Audit Reports') }}</span>
                            </a>
                        </li>
                        @endif
                        <li>
                            <a href="{{ route('pasax.dashboard.sessions') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.sessions*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                <span>{{ __('Sessions') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pasax.dashboard.tickets') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.tickets*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="22 12 16 12 14 15 10 15 8 12 2 12" />
                                    <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z" />
                                </svg>
                                <span>{{ __('Appeals') }}</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pasax.dashboard.settings') }}"
                                class="sec-nav-item {{ request()->routeIs('pasax.dashboard.settings*') ? 'active' : '' }}">
                                <svg class="sec-nav-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                                </svg>
                                <span>{{ __('Settings') }}</span>
                            </a>
                        </li>
                    </ul>
                </nav>

                <button type="button" class="sec-theme-toggle" data-sec-theme-toggle aria-label="{{ __('Toggle dark mode') }}">
                    <svg data-sec-theme-icon="moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                    </svg>
                    <svg data-sec-theme-icon="sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display: none;">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                    </svg>
                    <span data-sec-theme-label>{{ __('Dark mode') }}</span>
                </button>
                <a href="https://pasax.is-a.dev/docs/pasax-secure-monitor"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="sec-sidebar-version"
                    title="{{ __('Versi Pasax aktif: v:version — Klik untuk melihat catatan rilis', ['version' => \Pasax\SecureMonitor\Services\PasaxService::VERSION]) }}">
                    <span class="sec-sidebar-version-brand">
                        <svg class="sec-sidebar-version-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24" aria-hidden="true">
                            <image href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAdkklEQVR42q2beZhU1dXuf3ufU1U9N00zD80UBkEgOIAKURRj9DrFOHx5NA7RkC+DmsR8QY35jNGYKNFoIpn0GuQzuYkaTaKiURkEUVAEVLCBRmYa6IGeu6Zzzl73j1PDOVWFuX/cfp6i6XNOVe299tprvetd71aU+BERBWillAfQ3t4+tb6+/mInHT9nw/tNU9/Z+Mng97cdjDXtbaWlvZueviSO4+GhAAVKgUDmn8xvBUqDGNDa/xtAW/797KPZ67kLmc9TCkT8F4JtKSrLyxhSX8nkcUOYPaMhddbsiW2zZ41tjJVXr9q/f/9LY8eObczMxwKMUkoK56pKTF4rpQzArl17Tx83ZuhtiaRzwYq3dlQ++ec1rHt/F13t3f74IhZYGrTyJ6WOZwBQKFBZUyj/PZlrwWFIqSFmnxEJ28cY8Ay4HmhNdW0l80+bzLWXncq58yb219VUvLq16dAvZ0yduL5wbiUNkH3g4WefLb/mzPmLhwyqvvndLfu57a6nWP/2Dg+lFFVlyopEVHYykvlHMpNX2dXyPy9zLecDeQMEngsZ4NPXp/hO7mMUxvWE/pTgicyZN9l6+L+vYs7MkbQd61niuu6ihoaGRKERVOHk16xZM3ratGnP19fXn3r3g38x9z3wnCBaW7WVCsB4JuCWgRXL/J2dXNYBggssmf+o7PPBCarwfVEqY7DMbyk2TymjaO1/v9ebEJQyP7jlPLX4h1/WPV3dG3fuOnT57NnTDwaNoIKTX/HOOyM/O2nSmqqqignXfOMx5/m/vh3R9dUoy8pMHCQ7JAUonffK0Ir6Hy0lFjK3+jl/yDyr8tvEdxNVeitkPS4YI0Ju7F+yLI0YwXT0ccllpzp/WbIwEo/HdycS6qyGhkHN2TlrEVHPPfecWr16ddm0ceP+WV5eNuHiryx2nn/m7UhkSC2iFJ7r5YJP1gh+PJKce/vX/IEJAkryIaHY3QpW0wTNFXpGRDJbSQi6lSr0oMB+UIDnGQQhMriaF/++MXLxjUucurq6CZGI88+lS5eWAUpElBIRSynlHTp06NcjR4685aqFDznP/WltJDJ0AK5rclaVggCd3+vBQCbhv0OrmI0CxYGP0JaQwLNFGz3wHSoUMSW/zwqMK0RsC6eth6uumec8s+SbkQMHDj02ZszoW0XEUgJqV9Pez44bO2TT4t/+y9x151M6MrhWuZ6Eo3LOA1Vgv+kC1y8YeKELhD5ICiZO+HpgGwXHkP+KQgPpzNYATMDDMl9nWxZOR5/cf+9VZtHCBXrv4c6TJ44d+YEC6Opofe7g0fgVJ81f5BqtbbEtQl5XKvwKkHIKXOM4gTsY7VVBvgsaUCSfWrKp1bZQEQvLtlAoPCOImIA3BbeC8refFOdTrRVKAMd1N712tz1uZOXfagcMvlI1Ne2fMGxo9dbLbvp12cp/bcGuq1aekePk5GyEBiXC5PHDqagow0g4LeWWKjhJKTSGhAwkAhEtVEc1nhE6Eg798RSd3XE6u+PQn/LfW1VOJGbjeX4cCmGPjAEUKp81Ak5pWRZeT1zOnD+Vl/6wMNnV4063J04c+qUVa7aXr/zXZs8aUGV5xuRcLzvZkCWVwkukmXTCKLaufhDbtvj/9dPnwmv7HaojivMabFzHpbM7zqEjHWz5eB9vrN3GG2u2cay5DSrLsctjeJ7JGFiFsozKpdX8/jHGYNVUqLWrt3vvfNBc/vm5Y75kpxOpBX94ehV4xg9smVVTKCSDHJUEDKEUGKGiPIptWxhjcteNEWztu7nrie92gbiUzx5ZtwQrgwj39Xic+0wnu9s9SLlcNDnG8/8xiMGDahg8qIZZ08dy45fnc6Slk6efW8sjj7/K0YPt2ANrMNlMkYPMJpMNVEGy9A2jjMfv/89azplz3QK9aeuBaSvWNUJ1ufY8k3erwN6SopwseMZkMEA++tqWxhHodwQ7k4cdTzBGMpPV2JYmYvsvS2u6E4atB+Lc/I82du+NE3NTRNwUL69u4e9buhERUmkPx/VwXY/hQ+tYdPOlbFnxANd95Rzczj6USCYtFiYAP73mY6b46bEyqlev38l7H+6bZr+xduvgrpZOdGWZMsFwKwVAQ/Ifkr2awwCisLTi8cYUD21Mkkg43Dgtxk8+V012g6Rdw/72FI3NSbYeSvDRwQR7WpK093lYjsMhY2OXxXB6XSLGEO2J09WdRKhFa4Vt+eMwxuC6hqFDBrDssW8xc8Z4vn/XU1gVZaAtP0AqVRCwJYQvdMRSPe1drFrfNNh+d8ueWHYyufweBCMqCDCKo6Jn/NV++2Ca/3yhE7SA43Lv7h6OHuzDjTs0Hk7Q1uPguIYBFRZjB8eYNqqcy08ZwOThZUwZUcYvlx/hrif2oWssUl1JOJZkWNk4tAI3FC8V0aiVrYa4beH51NWUc+O3f4tdXYnJzVeVMERmG/g4jfVb9sbsj5ua8dOeFCHKUqk9HMnzz63c2Yfd0YdVrjBJF9WfZs3WJBeeWMNNZ9Yzs6GczwyNUVdpl6q/+f6Fw9m8pZ1XVh1m7qmDmDN5JDc+dYRXhlUxZ3wFjmuwtL+Hu5LCvW/3sfWIw7ljLG7/j7Noae/mzh8uwx48wA+MAqADlWSu5vG9NmKxdedh7Nb2XrB13mq5aRXWZRJw/uK6ZPogG7e5G6nWSMrFHOhj2vkjePiaUaHnjBE8kRBoNEaIRSwSaeG+b0zm+9eMB2D8uk7OvH8X6340kVPHVZByDBFbcdOLHbywKQ5RYcXbCcrEcMe3L2HFWx+z8vXN2HXVeF4GvmsdQoh+ihSwLNo74+hU2gWtw/MpADcqUHrkHsnctpUPTi6dUc2tXxiIHOwi2tbDty4bRsLACYu203g4mYsDAFYmlljKD5zRiMW+DsPmeIyvXjYWx/FIpFxunFfH764fxbz7mti0L04soulLGdbu7CEqKcoTcex4nBfXtyICv77vesqqyzCOmw3fgbrDBBbW39ppx0XnXb941fN1ffY9+X0V2gTip7xFFw1nVK3Nh4+fxm++ewKv3DWZK+YM4PQfN/HHtR1EbY1C0FoTsS201jQf7eLjnQd54E+bmD0izcAKTSRiUR6zfSOcWc9j147ktB/vYMeRFEc7UrQ3tpM+2IXT1od7uJvZw6IoBVMnjuDqK8/CdPdjWTozNuND44zX+cUVOXbJDge7bIWnslX4v6nAMwBDBAt4ZVsfY6cPZVJDJWnHoDXcd/lw5k+t5iu/2c+KbT0s+9ZYUn0JHn96JX97eSPb9xyhJ57EeIba6nJOXjmUKy86la9fdy4DB1QST7p8/ezBVMQszrh3J8NqNHd+YQirGjvZ8n4bsyfWcOcVDTiuh21ZfOu6BTz151V+BZtjoSTvEQVj1yU5wXDcCNZaxekx8LNmV5zTp1RjMmWypRWOa1hwQhXbHpxM3EQ549Y3mHP+HXz/v55g/cZGuroSmJQNXhndPWk2f7SLO3+0jJPOuZ3lKz+goszGGMO2Awm0azjaluLWy0ayYfEsli6ahtgWNZU2oHCNYdaJY5k5cywmnvTJERE/exV5rj8vTSl2JrR3gjFPcveCvmFp33M+PJTg3KlV6AwUBbAtRcrxqK+0uXbcft5/6nEadx9FDRoIUkttjcOMCa3MGN9CTVUaTA26vo79h9u56Oqf8/u/vMX8xft5/YMuGh+exp1fGslnvvcxh7scrjxvBM26jM17+4nYmnTaQ2vFgrknQjLl047ZuQS8XAWQoV2U64LlvARhT3bvhOt8g2BpzSctKfpcmD2+wrds5hnPM8QiFhs/2M1VX32ESK2N45VRH+3nnms/4rLZBxlRnQDgcEc5z6wfwz3PT6fHriBiO3zz60u44b7bWfrASXie4QcXDaO9z+OkH+5g/69OZO6kSl7Y3MPMMZVk6YvZn50AWiHGFCxqkHz1vVQXkQgiIS63yG0KPD/7HW82xWmoj1JTbuF5kgNWOlMjfOfuZZi0gzFRRtd0su6Hr/Ht83YyIhpHEiBxGFEW53sXbmfVbW8wMNILnoVlp9F71gHgeIZk2vDgl0dy4axaPvfTnZx7Yg1rP+7B0lBb5qfzCeOGoioyhVLR9pY85hEJxIBCHj/j5goJBJHifW8yr9U7+zl9fIWfcCSLEg3K0qx7dzvr123Dqq3AOC5Lr1vP5KE9pJptTK+GfiAOpl+RbLY4eXwnD31pM07cIJUxlv1jI3v2txKL2ogI/SmPJxc2MG5QlIdfaWMfNre+1M6G5jRKQV1tNZVVZYjnFWQ3KWKMNIV1vArTlaHWRhBbZzwlZms0wtaDCa6aVY2IbwDPiB8MgeUrt0DawUtHmDvxCAsmtOK2aqKOh0oIJBQkFCqpiLqCaVVcPfUAo4d0YySK19PDu1s+QQExGypjfoXxzC3jiQ+q5KCJ8tjqLub/aj+bDiYZWB0jEo1mAmAQvElRs8YOdWAy+0QUgZI4+J78PjLiJ8oNrR73vJukqbKWtzvgpHEQi2Tsaik6+j02fLAPFbMQB84a3YakQZKZDSgSormUEYxALOYxY3AnBzvqwPNoPtxKwhE+OZzgUJfLvtYUmw8kaOl20a5QplwSR/r587pjPPjFOoyR8LhD9U22USHYYayvioug0OV8hIxZQnO/4fyX+unuclG24tZ/dtDW0o9yDJsPJtl/LEVvUnG0qQsrpnGThmrlQBL/pQpqjaxnGT99WRl3sissHny5jd/tbcJLxamIWQyuthlabVMWd+jrTZN2XeRQF2OqRpJMu6TTjk+rhZghlS/2MiW/HU6Bkq/vC/J/Pov4g6uKKFbuTdF9OE4sYnD609gJh9+80cOCSZXMGVfB1+bWMnN0BQv31LDiNRcisLujCpXIGMCScG2RASBaBM/VbG+vBtvDTRq+8b/G8eXLxlJfLgyujWFlyuMv/Ggrrzd242nDBWcM5GvnDuGTvYdJJlJ+eVxAi4UdXmEHVztEIGWvKx1wgvz+SXvC+CoFrb2kLCFiPJzd3fxgYQN3fHFEaJ/NOnEcK17egK7yWL5rGP3dESqjLiatffsb8SMpkDaKWJXh1aah7GqtRcUc7MoKvnbJdMaMjOE4HknHI4rF+h29bPukl7cemklVuWZKQyVlMYs9B1qQeBKrtsrfCgEjZwO6Pz0PHVzw4PpLQWsznyh8w/QkDfPGxLjjnGrKO/owR7s5e0YlS15rY9nbxwCIJz2MwBfPPxViUWztcrirmkVrT/S93QUcAQdwwUsrYpbHsc4Y31k9EytqIb39nPO5GYwePZS+hINr/KWKWIrv/s9+vnnVWObNqGP6hBrSrs9SfbhtHziuj0Uk3GgpLOR1YWEvhR2c7L4UKYqiRoSfXzaMd+6eyHDbY/k901h2y3huffowdz53mIoyG8Rw2smTWLBgFuljvaioZu2hgUgatAi4gAfGU9iWx/tHB7Lgb/PY1TUAS6dRkSg//v5VucF7Rqgos3hyVStdCWHRJcPoT7okUh6W9uv/tRs+BtvO8IRhjBMsCMMGKAC8IeggJmw9lW9BGRE++5kqqsbUs7U5wYKp1Xz408k8/14XF/1yD674nPyjP72RgUPrkH6Pi8a3oSOC6ygwYFywbI871p/InL+ew4ddg7CjHunWHu7/yfWcfspE4knXryItRU/c5YfPHOKR60YTtfP9ybKYxe59rWx4vwlVWebzfyIlWnHBYqiotjnew1KyNvSNqqiqidDc42IMjB0UYesDJ+AZmHFXEwc6Upw4eRRPLPku4DF/WDt4PtQ2rkJbhkOdFTy6bTJENWU6gUokuftnX+POW75If8LB0grXCLGoxY+ebWZ6QwWXnlJHX8LF0grP+IzRcy+tJ9HajR2xM3EtS4sWdqkkUAxlgp4qgXZFpGSzJ5tas2BnWG2UfcdctIZ42hBR8Op/jeeCGdVMv3077+1PUVsVRUe7mT2oE5KgDYjnO9SyT8aR6jAYN0GSkTz2h7v5yQ+upLs/jaBwPCiPWjQ2J1j6VgdLbhiT612KCFHboqcvxeNPvwEV5eHgF+wvSqDIz/EBQSSsQsnvOHxQYLtkbo2ss9nX4eQYH08E1xUeuXokU4ZFufbJw5Rve5e5QzqpK0tikhZKBAvBSUX43ZZBXHDZHO76zsUs31vDfWu6iQ1t44YzB+cLDq24eek+bpw/iCkjy+mNu9iWwnE8aqqiPPrEcvZt25/hBSXHZ6hQUzWcCu0CjUix1Uqk0FzRJPnnR9dF2HwoFfoUz/j9g/88ZzCvfNDFi29u58HTe8AInqdQRmFFXF7aPZTm1DBW338dE8cPZ+5pwumTK1n4xEFWNfZx60XD+c2WOHsOJTgQ17x4+TCSaQ9Lg+sZKiui7Nx9hJ/94hl0TUVm9TlOdzlgAyM52rRUwvsUilQI8iwAowdGaOnzMn1CheMJZVGL1h6X2ffvoXFPG7GqY5wxqANcUB4oT1BoFm8eyrxzT2biuGF096Xo6fe4+KQ6tv1iKl0Jw5zfHuGpDT2sbeqnLW5YvzdB1NY4nvGbMa7LDTc/Rm9XPyoaQULVfVhYEdZjKXQR8SH5iklK6j2kqGsqwOg6m96UlyEh/P26ansfJ97eyJlTavjvsyGWOszJ9b3gaDBgaY8dbQN4t3UYt91wlj8gpYjY0N3vMKjK4udXj8J4HjEnRYWXon9/J8s3HvNrea0pi1nccPMSNqzdij2gKqdkKRZe5cUdIdTJcfe3ZOKn5MmQApMEjdNQZ3PMUWxsThGLaO5/uYVLH93L4mtG8tDlg/jHqu2cMqCd8lgKN6X8/K/hka3DGTJlChecPZNEykNb/prYliKdNlRYUBFPkNrfTby5G1p6mDUiglIKW8PVX3+UZ/60Eru+Fs81PnJVqhj1BojQIBiwjxcf/MIwgw0liBPz4EIQLK3oShm+/laaZrucs5a1MTPi0HYsxZq7JnBSQwWJZJp1G5u4c0w3uGAciGiPvng5T+8cwn/9eC5l5TF6+tLYdh56pz1h7MAoS68ewY/+uJuedocbvzqO688bReOuZhbe+hveWfsR9qBaXM8L1TEikofzQbZLAmSvkC2GioJjwOED8SBbDmcCoOtBWVTxP40pVjUmiGiPRFeSDV1xDi+ewvABUZKOsOOTI7Qd2MN5k7sgBWIUKir8eccwElUN3HTlXJxMNzksERJ64mmuOHUAF510CrGYxsLwy9+9xL2Ln6H7WC92fS2ua0LyvFALukCXEe75S6YYyrXCVVEfVEqRokhA86A41p2G7jh2xCD9SdzWPv73ila+ff4QBlZFWfP+boZ4Rzmhug9JaSJiwLN56KMhXHDBLEaPGkx3Xwrb8sURWmksSxON6hxQ6eno4p+vvMujf1jOjg/3QE0F1oAqXE9QQeVpQKQpQWVakbomS4iEqkEJOnrJvB/8Uys/1V0zrZwnXmvhyK5uiKe55LR6VjT28/s3t3PFvCG89+o2zhncgcYllbaJRV3WHRnMJ04Dz912CVor6mrKch/tetDTG6fpk3a2fLSHN9Z8yOtvfkDb/lYoi2LV1/jMk5cdraK01FQF7pcGdHYhJX48zWYxo+5fcVxhYr3Nuu818Mp7xxg+IMoXTx+EZcHbTXF+v/IIG9bt4DvTenz3TwtoWLJnDHZlJb9+/EWMsjFiSKVcevsStHf0cKSlkyOtXXjd/f6XVZZh19diRPxIrwLS3MJOvkgJeF96Pe1MTzynpigMhBQRQ3lJbDYzxNNCw8AoN180EoD+pIuXNMydVMlAz+KZew9zRnUXJCFmDAc7Knm5bSSu28/SP74ebsUp7TcabAsiFlZdNQqfWfayNFd28kqHpHUSkPAFZcXHl9oKthLJc4CqyINCxitWfvom0wpSriGR9iNxVhrjGeGvK5tocFtoiMZxkhYR2+Op5jH0p8qxY2kYWFPEjWXFkSL4FDuSn6wK8LkSbNbksUp28iKZBo0cp8Engl1WFiWedHyrZ6ygpFSTOEwtZweYV7UptKVCOkZLK958r4mza4/5GiRXk3BtnmwZA5aHEYXxTN6zVL4voQIdmpwUR+liPw4EPFWqsJdCZ/Y7Pn4BpdFDB9f6gyPYAS5UXlLyi0OGlXwvQUSIRGx6ehJ8+FETF9Z3QwJsY/hn2wj2xwdgWV6egQq6s7JQysq4uUYp7Uf5EJkZ7l3IcVxc5dCKBEJlZmaOx/AhA9DTJo9EOW5AGiOFIpDS6E+Vwo552UwsotnUeBDnyD4+V50JgA481jI+pyqX0FBV+HBESS1wMQxXn1qzECZ5gzZMO0yfMgI9//QTUpKDusVBQKnSqVCOkygFcj25Fe/tZoo5Qr1OIS68213P+r4haNvFC8nmg5LbAFwN/g7sNzGmQCdcXL1KKX4rsF1EK846bVJKn33G1Lb64YMw6bSoLDUrAS9Q6jiJUIVpwxLCitUbdnBuRSekBeXAb9snINhoJPweVag7KlSIF7NSUjpRleiDSNHhCpNMy+AR9SyYO7VNnzhl5Mfnz58JvQmjs6qKEoxQqBrMKC+kIApnb8eiNsc6+ti+dSefL+9EEnCgv5IX+kagtIuXSaMqyEEVTFiVcO5SqU0VqVUIZAVChZEY8avV3n5zwfzpzJg65mMdLY+t/No1Z0PEQjxT5O4qFDqkSEImBbjBZPf/jsOoowc4NdKPSsLSrjH0mwoslZXiBhsiYUo6CF1z2UaKGlWFbc/AKqsiek9hUBgfRMVsFn7lbFCyUm/atuuFUz87JnHhJXO019UrlpWRmuZEBZJjgAtV2K5ncDMKTtfzX2nHxfUMq9/fxbRkM1XGoS9h88fEGNCefzSi0I6F2kSCNPzxAHle9pLtYufeW3waym+12RrT1SdXXjlPnzKjIbF95+EX9CnTp+9Op53lD99zg4pVV3iScgLgQfJBBwlhAq01NVUxaqtiDKguozbz//oB5diWZu37nzDX6sFKCf9IjuKA1GEr46c+lXXwAjGjKi7kwoeO8hgkB53keK4QnrxSYBJpqgZVeQ/dc71KJFPLp0+ftNsWEdXYuOtnkyc1XP7APdeq733ndxIZXKvcgIggiIoMgoraHDjcwQOP/Z3a6gq8jNBaRLAsTXd3H++v20xn2SSGpmI86k1EKYM5LjDNdp2zKm8pquDCML0EbFclGMxcPDC+4rSvXx566GY1dFCV7NjT/DMgf2Rm7979vx47tuGWG2551Fn2+1cikaF1uLmDUkVqU/AEevuPj7RrKkBH/HIDFz/DBCelCo7Q6aKAGmZ0Clib4NkLpOhaViwNgq01Tmsn3110ufPITxeGjszYgHn22Wetffv2LIrFImf88Ve3nNzd1ef8469rI5EhdRlVpxTJZ5WlsOpri1mUjMt6xqDEw1IeniifLFV5VkZCICUrZAyr0MJGPz4IU8FjONl7RlA6I11u6eSmmy90Hrnvq5EjR45uamgYtejZZ5+1AGMrpURERCmV3LJly6XGyJrnn7p9wk21lc5TT7wWYUAltm37EwqJJwXP9UqyhBLA8W5xKi8GUVLcvCh5UuVTj1JKKIhatsZNpDHxFLfdcaXz8H1fjbS0HNu9b9/RS0eMGJ7MHJsTnVFgGxHRs2bNat59uPnszq6ejUuXfC+y+Bc3majneW5nr6jMWbwcNRZoOakShxNUFnWJlMieEjhAUdB4LUFcqsw19amyTUEpwbIUIga3vVsGVEa9J5+4xTx8302RtrbOjW1tLWefccas5qKDk4WnR599553y+Z+ZuHhgXdXN23cd5Y57lrH85Xc9XE9RXaGsaERlz/kEkZmSwla7BIKVysndgw2VYnCpPrWOz8HlLFTKNmmNh0mlhd6EqOoyueaKedbPf3w9w4dU0dEVXzJ40MBFSqnjH50tdXj6o48aT588seE2z8gFGzbtrnziqTd4fe1WjrV0+G+N2BCxUEpnUlipgCBFkOTf/6jik+eFt0T8lpnrgeNBRDOmYQgXf/5kbrz2HKZNHt6fTrmvHmxu++XU/9fD08c7Pr9tW9PUadPGX+w4qXM2fbB76ptvNQ5++72dse27j3CktYtk2sshRaV1zhAh4U2Oqyzmn/PbR8LnCiXcg8xmDK38ZmhtTRmjhtUxa1oDp588IfW5udPaPjNhRCOet2rXntaXJk3698fn/y8eMbxIBsZ/5wAAAABJRU5ErkJggg==" xlink:href="data:image/png;base64,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" width="24" height="24" preserveAspectRatio="xMidYMid meet" />
                        </svg>
                        <span>Pasax</span>
                    </span>
                    <span class="sec-sidebar-version-badge">v{{ \Pasax\SecureMonitor\Services\PasaxService::VERSION }}</span>
                </a>
            </aside>


            <main class="sec-main">
                @php
                $flashToasts = [];
                if (session()->has('toast')) {
                $t = session('toast');
                if (is_array($t)) {
                $flashToasts[] = [
                'type' => $t['type'] ?? 'success',
                'title' => $t['title'] ?? (($t['type'] ?? 'success') === 'error' ? __('Gagal') : __('Berhasil')),
                'message' => (string) ($t['message'] ?? ''),
                ];
                } else {
                $flashToasts[] = ['type' => 'success', 'title' => __('Berhasil'), 'message' => (string) $t];
                }
                }
                if (session()->has('security_message')) {
                $flashToasts[] = ['type' => 'success', 'title' => __('Berhasil'), 'message' => (string) session('security_message')];
                }
                if (session()->has('success')) {
                $flashToasts[] = ['type' => 'success', 'title' => __('Berhasil'), 'message' => (string) session('success')];
                }
                if (session()->has('error')) {
                $flashToasts[] = ['type' => 'error', 'title' => __('Gagal'), 'message' => (string) session('error')];
                }
                if (session()->has('status') && ! session()->has('toast')) {
                $flashToasts[] = ['type' => 'info', 'title' => __('Status'), 'message' => (string) session('status')];
                }
                if (session()->has('message') && ! session()->has('toast')) {
                $flashToasts[] = ['type' => 'info', 'title' => __('Informasi'), 'message' => (string) session('message')];
                }
                if (isset($errors) && $errors->any()) {
                foreach ($errors->all() as $err) {
                $flashToasts[] = ['type' => 'error', 'title' => __('Gagal Validasi'), 'message' => (string) $err];
                }
                }
                @endphp
                @if (session()->has('security_message'))
                <div class="sec-alert sec-alert-success">
                    <span>{{ session('security_message') }}</span>
                </div>
                @endif
                @if (session()->has('toast'))
                <div class="sec-alert sec-alert-{{ (session('toast.type', 'success') === 'success') ? 'success' : 'danger' }}">
                    <span>{{ session('toast.message') }}</span>
                </div>
                @endif

                <header class="sec-header">
                    <div class="sec-header-brand">
                        @yield('header_left')
                    </div>
                    <div class="sec-header-actions">
                        @yield('header_actions')

                        <button type="button" class="sec-support-btn" onclick="window.pasaxOpenPageSupport ? window.pasaxOpenPageSupport() : null;" title="{{ __('Panduan & Informasi Halaman') }}" data-sec-header-support>
                            <svg class="sec-support-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                                <line x1="12" y1="17" x2="12.01" y2="17"/>
                            </svg>
                            <span>{{ __('Support') }}</span>
                        </button>
                                                <div class="sec-client-ip-badge" title="{{ __('Waktu Server (:tz)', ['tz' => $pasaxTimezone]) }}" style="cursor: default;">
                            <span class="sec-ip-dot" style="background-color: var(--sec-primary);" aria-hidden="true"></span>
                            <span class="sec-ip-label">{{ __('Server:') }}</span>
                            <span class="sec-ip-address">{{ \Pasax\SecureMonitor\Support\DashboardView::formatDate(now(), 'H:i') }} <span style="font-size: 10.5px; opacity: 0.75;">{{ \Pasax\SecureMonitor\Support\DashboardView::formatDate(now(), 'T') }}</span></span>
                        </div>
                        <div class="sec-client-ip-badge" onclick="window.pasaxCopyIp ? window.pasaxCopyIp('{{ request()->ip() }}') : null;" title="{{ __('Alamat IP Anda saat ini: :ip (Klik untuk menyalin)', ['ip' => request()->ip()]) }}" role="button" tabindex="0">
                            <span class="sec-ip-dot" aria-hidden="true"></span>
                            <span class="sec-ip-label">{{ __('IP Anda:') }}</span>
                            <span class="sec-ip-address">{{ request()->ip() }}</span>
                        </div>
                        <button type="button" class="sec-refresh-btn" onclick="window.pasaxRefreshPage ? window.pasaxRefreshPage(this) : window.location.reload();" title="{{ __('Muat ulang data tampilan & bersihkan cache (optimize:clear)') }}" data-sec-header-refresh>
                            <svg class="sec-refresh-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                <path d="M3 3v5h5"/>
                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                <path d="M16 21h5v-5"/>
                            </svg>
                            <span>{{ __('Refresh') }}</span>
                        </button>
                        <div class="sec-lang-switcher" role="group" aria-label="{{ __('Pilih Bahasa / Select Language') }}" title="{{ __('Ganti Bahasa / Switch Language') }}">
                            <button type="button" class="sec-lang-btn {{ $currentLocale === 'en' ? 'is-active' : '' }}" onclick="window.pasaxSetLocale('en');" title="English" aria-pressed="{{ $currentLocale === 'en' ? 'true' : 'false' }}">EN</button>
                            <span class="sec-lang-sep" aria-hidden="true">/</span>
                            <button type="button" class="sec-lang-btn {{ $currentLocale === 'id' ? 'is-active' : '' }}" onclick="window.pasaxSetLocale('id');" title="Bahasa Indonesia" aria-pressed="{{ $currentLocale === 'id' ? 'true' : 'false' }}">ID</button>
                        </div>
                        @if (\Illuminate\Support\Facades\Route::has('pasax.dashboard.password.update'))
                        <button type="button" class="sec-btn sec-btn-ghost sec-btn-sm" onclick="window.pasaxOpenPasswordModal ? window.pasaxOpenPasswordModal() : null;" title="{{ __('Ubah Password Dasbor') }}" style="height: 32px; padding: 0 10px; font-size: 12px; gap: 5px; color: {{ session('pasax_dashboard_needs_password_change') ? 'var(--sec-warning)' : 'var(--sec-text-muted)' }}; border: 1px solid {{ session('pasax_dashboard_needs_password_change') ? 'var(--sec-warning)' : 'var(--sec-border)' }}; border-radius: var(--sec-radius-sm); position: relative;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <span>{{ __('Password') }}</span>
                            @if (session('pasax_dashboard_needs_password_change'))
                            <span style="display: inline-block; width: 6px; height: 6px; border-radius: 50%; background: var(--sec-warning);" title="{{ __('Password masih default (Admin)') }}"></span>
                            @endif
                        </button>
                        @endif
                        @if (\Illuminate\Support\Facades\Route::has('pasax.dashboard.logout'))
                        <form method="POST" action="{{ route('pasax.dashboard.logout') }}" style="display: inline-flex; margin: 0;" data-sec-confirm="{{ __('Keluar dari sesi dashboard Pasax?') }}" data-confirm-title="{{ __('Konfirmasi Logout') }}" data-confirm-type="warning" data-confirm-btn="{{ __('Ya, Logout') }}">
                            @csrf
                            <button type="submit" class="sec-btn sec-btn-ghost sec-btn-sm" title="{{ __('Keluar dari sesi Pasax (:user)', ['user' => session('pasax_dashboard_user', 'Admin')]) }}" style="height: 32px; padding: 0 10px; font-size: 12px; gap: 5px; color: var(--sec-text-muted); border: 1px solid var(--sec-border); border-radius: var(--sec-radius-sm);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                    <polyline points="16 17 21 12 16 7"/>
                                    <line x1="21" y1="12" x2="9" y2="12"/>
                                </svg>
                                <span>{{ __('Logout') }}</span>
                            </button>
                        </form>
                        @endif
                    </div>
                </header>

                @if (! empty($heading) || ! empty($subheading))
                <div class="sec-page-header">
                    @if (! empty($heading))
                    <h1 class="sec-title">{{ $heading }}</h1>
                    @endif
                    @if (! empty($subheading))
                    <p class="sec-subtitle">{{ $subheading }}</p>
                    @endif
                </div>
                @endif

                @if (session('pasax_dashboard_needs_password_change'))
                <div class="sec-alert sec-alert-warning" style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 18px; border-radius: var(--sec-radius); background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); color: var(--sec-warning);">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; line-height: 1.45;">
                        <span style="font-size: 16px; line-height: 1;">{{ session('pasax_dashboard_is_emergency_login') ? '🚨' : '⚠️' }}</span>
                        <span>
                            @if (session('pasax_dashboard_is_emergency_login'))
                                <strong>{{ __('Mode Pemulihan Darurat Aktif:') }}</strong> {{ __('Anda berhasil masuk menggunakan PASAX_DASHBOARD_SECRET. Segera buat password baru untuk mengamankan dasbor.') }}
                            @else
                                <strong>{{ __('Peringatan Keamanan:') }}</strong> {{ __('Anda masih menggunakan password bawaan (Admin). Segera perbarui password dasbor untuk melindungi server.') }}
                            @endif
                        </span>
                    </div>
                    <button type="button" class="sec-btn sec-btn-sm" onclick="window.pasaxOpenPasswordModal ? window.pasaxOpenPasswordModal() : null;" style="background: var(--sec-warning); color: #000; font-weight: 700; white-space: nowrap; height: 30px; font-size: 12px; padding: 0 12px; border-radius: 6px; border: none; cursor: pointer;">
                        {{ __('Ubah Password') }}
                    </button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>

        <!-- Pengumuman kunjungan pertama (hanya local + debug) -->
        @if (app()->environment('local') && config('app.debug'))
        <div class="sec-modal-backdrop" id="pasaxAnnounce" hidden>
            <div class="sec-modal-box" role="dialog" aria-modal="true" aria-labelledby="pasaxAnnounceTitle">
                <div class="sec-modal-header">
                    <h3 class="sec-card-title" id="pasaxAnnounceTitle">👋 {{ __('Selamat datang di dashboard Pasax') }}</h3>
                </div>
                <div class="sec-modal-body" style="font-size: 14px; color: var(--sec-text-muted); line-height: 1.6;">
                    <div>🛡️ {{ __('Bawaan instalasi adalah monitoring saja: ancaman dideteksi dan dicatat tanpa blokir otomatis.') }}</div>
                    <div>⚙️ {{ __('Siap karantina otomatis? Atur di halaman Settings atau via API.') }}</div>
                    <div>📖 {{ __('Panduan lengkap:') }} <a href="https://github.com/pasax-dev/pasax-secure-monitor-laravel" target="_blank" rel="noopener" style="color: var(--sec-primary);">github.com/pasax-dev/pasax-secure-monitor-laravel</a></div>
                </div>
                <div class="sec-modal-footer">
                    <button type="button" class="sec-btn sec-btn-primary" data-pasax-announce-close>{{ __('Mengerti') }}</button>
                </div>
            </div>
        </div>
        <script>
            (function() {
                try {
                    var key = 'pasax-announce-{{ str_replace('.', '-', \Pasax\SecureMonitor\Services\PasaxService::VERSION) }}';
                    if (window.localStorage && localStorage.getItem(key)) return;
                    var el = document.getElementById('pasaxAnnounce');
                    if (!el) return;
                    el.hidden = false;
                    var btn = el.querySelector('[data-pasax-announce-close]');
                    if (btn) btn.addEventListener('click', function() {
                        el.hidden = true;
                        try {
                            localStorage.setItem(key, '1');
                        } catch (e) {}
                    });
                } catch (e) {}
            })();
        </script>
        @endif
    </div>
    <script>
        // Toggle tema gelap/terang manual (tersimpan di localStorage, default ikuti sistem).
        (function() {
            var key = 'pasax-theme';
            var labels = {
                dark: {!! json_encode(__('Dark')) !!},
                light: {!! json_encode(__('Light')) !!},
                auto: {!! json_encode(__('Auto')) !!}
            };

            function stored() {
                try {
                    return localStorage.getItem(key) || 'auto';
                } catch (e) {
                    return 'auto';
                }
            }

            function isDark(mode) {
                if (mode === 'dark') {
                    return true;
                }
                if (mode === 'light') {
                    return false;
                }

                return window.matchMedia('(prefers-color-scheme: dark)').matches;
            }

            function paint() {
                var mode = stored();
                var dark = isDark(mode);
                var root = document.documentElement;

                if (mode === 'auto') {
                    root.removeAttribute('data-theme');
                } else {
                    root.setAttribute('data-theme', mode);
                }

                if (dark) {
                    root.classList.add('dark');
                } else {
                    root.classList.remove('dark');
                }

                document.querySelectorAll('[data-sec-theme-toggle]').forEach(function(btn) {
                    var moon = btn.querySelector('[data-sec-theme-icon="moon"]');
                    var sun = btn.querySelector('[data-sec-theme-icon="sun"]');
                    var label = btn.querySelector('[data-sec-theme-label]');

                    if (moon) {
                        moon.style.display = dark ? 'none' : '';
                    }
                    if (sun) {
                        sun.style.display = dark ? '' : 'none';
                    }
                    if (label) {
                        label.textContent = labels[mode] || labels.auto;
                    }
                });
            }

            document.querySelectorAll('[data-sec-theme-toggle]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var order = ['auto', 'light', 'dark'];
                    var next = order[(order.indexOf(stored()) + 1) % order.length];

                    try {
                        localStorage.setItem(key, next);
                    } catch (e) {
                        /* abaikan */ }

                    paint();
                });
            });

            if (window.matchMedia) {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', paint);
            }

            paint();
        })();
    </script>
    <script>
        window.PasaxToasts = @json($flashToasts ?? []);

        (function() {
            // Global Page Refresh Handler (triggers optimize:clear & reloads fresh state)
            window.pasaxRefreshPage = function(btn) {
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('is-refreshing');
                }

                var finishRefresh = function() {
                    if (typeof window.pasaxCustomRefresh === 'function') {
                        window.pasaxCustomRefresh(function() {
                            if (btn) {
                                btn.disabled = false;
                                btn.classList.remove('is-refreshing');
                            }
                        });
                        return;
                    }
                    window.location.reload();
                };

                var clearUrl = @json(\Illuminate\Support\Facades\Route::has('pasax.dashboard.optimize-clear') ? route('pasax.dashboard.optimize-clear') : null);
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

                if (!clearUrl) {
                    finishRefresh();
                    return;
                }

                fetch(clearUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    var msg = (data && data.message) || {!! json_encode(__('Cache aplikasi, tampilan, routes, dan konfigurasi berhasil dibersihkan.')) !!};

                    if (typeof window.pasaxCustomRefresh === 'function') {
                        if (window.pasaxToast) {
                            window.pasaxToast(msg, 'success');
                        }
                    }
                    finishRefresh();
                })
                .catch(function() {
                    finishRefresh();
                });
            };

            // Global Locale Switcher
            window.pasaxSetLocale = function(lang) {
                if (lang !== 'en' && lang !== 'id') return;
                try {
                    localStorage.setItem('pasax_locale', lang);
                    document.cookie = 'pasax_locale=' + lang + ';path=/;max-age=31536000;SameSite=Lax';
                } catch (e) {}
                var url = new URL(window.location.href);
                url.searchParams.set('lang', lang);
                window.location.href = url.toString();
            };

            // Copy Current Client IP Helper
            window.pasaxCopyIp = function(ip) {
                if (!ip) return;
                var notify = function() {
                    if (window.pasaxToast) {
                        window.pasaxToast({!! json_encode(__('Alamat IP berhasil disalin: ')) !!} + ip, 'info', {!! json_encode(__('IP Disalin')) !!});
                    }
                };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(ip).then(notify).catch(function() {
                        fallbackCopy(ip, notify);
                    });
                } else {
                    fallbackCopy(ip, notify);
                }

                function fallbackCopy(text, cb) {
                    var ta = document.createElement('textarea');
                    ta.value = text;
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.select();
                    try {
                        document.execCommand('copy');
                        cb();
                    } catch (e) {}
                    document.body.removeChild(ta);
                }
            };

            // 1. Toast Notification System
            window.pasaxToast = function(message, type, title) {
                if (!message) return;
                type = type || 'success';
                var stack = document.getElementById('pasaxToastStack');
                if (!stack) return;

                // Cegah penayangan toast ganda dengan pesan identik yang masih aktif
                var existingMsgs = stack.querySelectorAll('.sec-toast-message');
                for (var em = 0; em < existingMsgs.length; em++) {
                    if (existingMsgs[em].textContent.trim() === String(message).trim()) {
                        return;
                    }
                }

                var toast = document.createElement('div');
                toast.className = 'sec-toast sec-toast-' + type;

                var icons = {
                    success: '✓',
                    error: '✕',
                    danger: '✕',
                    warning: '⚠',
                    info: 'ℹ'
                };

                var defaultTitles = {
                    success: {!! json_encode(__('Berhasil')) !!},
                    error: {!! json_encode(__('Gagal')) !!},
                    danger: {!! json_encode(__('Gagal')) !!},
                    warning: {!! json_encode(__('Peringatan')) !!},
                    info: {!! json_encode(__('Informasi')) !!}
                };

                var displayTitle = title || defaultTitles[type] || '';

                toast.innerHTML =
                    '<div class="sec-toast-icon">' + (icons[type] || 'ℹ') + '</div>' +
                    '<div class="sec-toast-content">' +
                    (displayTitle ? '<div class="sec-toast-title">' + displayTitle + '</div>' : '') +
                    '<div class="sec-toast-message">' + message + '</div>' +
                    '</div>' +
                    '<button type="button" class="sec-toast-close" aria-label="' + {!! json_encode(__('Tutup')) !!} + '">&times;</button>';

                stack.appendChild(toast);

                var removeToast = function() {
                    toast.classList.add('sec-toast-hiding');
                    setTimeout(function() {
                        if (toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 200);
                };

                toast.querySelector('.sec-toast-close').addEventListener('click', removeToast);

                var timer = setTimeout(removeToast, 5000);
                toast.addEventListener('mouseenter', function() {
                    clearTimeout(timer);
                });
                toast.addEventListener('mouseleave', function() {
                    timer = setTimeout(removeToast, 3000);
                });
            };

            if (window.PasaxToasts && Array.isArray(window.PasaxToasts)) {
                window.PasaxToasts.forEach(function(t, i) {
                    setTimeout(function() {
                        window.pasaxToast(t.message, t.type, t.title);
                    }, i * 150);
                });
            }



            // 2. Alert-Dialog Confirm System
            var modal = document.getElementById('pasaxConfirmModal');
            if (!modal) return;

            var titleEl = document.getElementById('pasaxConfirmTitle');
            var msgEl = document.getElementById('pasaxConfirmMessage');
            var iconEl = document.getElementById('pasaxConfirmIcon');
            var okBtn = document.getElementById('pasaxConfirmOk');
            var cancelBtn = document.getElementById('pasaxConfirmCancel');

            function showConfirm(options, onConfirm) {
                titleEl.textContent = options.title || {!! json_encode(__('Konfirmasi Tindakan')) !!};
                msgEl.textContent = options.message || {!! json_encode(__('Apakah Anda yakin ingin melanjutkan tindakan ini?')) !!};
                okBtn.textContent = options.btnText || {!! json_encode(__('Ya, Lanjutkan')) !!};

                var type = options.type || 'primary';
                okBtn.className = 'sec-btn ' + (type === 'danger' ? 'sec-btn-danger' : (type === 'warning' ? 'sec-btn-danger' : 'sec-btn-primary'));

                if (type === 'danger') {
                    iconEl.textContent = '🚨';
                    iconEl.style.background = 'var(--sec-danger-bg)';
                    iconEl.style.color = 'var(--sec-danger)';
                    iconEl.style.border = '1px solid var(--sec-danger-border)';
                } else if (type === 'warning') {
                    iconEl.textContent = '⚠';
                    iconEl.style.background = 'var(--sec-warning-bg)';
                    iconEl.style.color = 'var(--sec-warning)';
                    iconEl.style.border = '1px solid var(--sec-warning-border)';
                } else {
                    iconEl.textContent = '🛡️';
                    iconEl.style.background = 'rgba(37, 99, 235, 0.1)';
                    iconEl.style.color = 'var(--sec-primary)';
                    iconEl.style.border = '1px solid rgba(37, 99, 235, 0.25)';
                }

                modal.hidden = false;
                okBtn.focus();

                var cleanup = function() {
                    modal.hidden = true;
                    okBtn.onclick = null;
                    cancelBtn.onclick = null;
                    document.removeEventListener('keydown', handleKey);
                };

                var handleKey = function(e) {
                    if (e.key === 'Escape') cleanup();
                };

                cancelBtn.onclick = cleanup;
                modal.onclick = function(e) {
                    if (e.target === modal) cleanup();
                };

                okBtn.onclick = function() {
                    cleanup();
                    if (onConfirm) onConfirm();
                };

                document.addEventListener('keydown', handleKey);
            }

            // Intercept form submissions
            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (!form || (form.method && form.method.toLowerCase() === 'get')) return;
                if (form.dataset.pasaxConfirmed === 'true') {
                    form.removeAttribute('data-pasax-confirmed');
                    return;
                }

                var submitBtn = e.submitter;

                var confirmMsg = form.getAttribute('data-sec-confirm');
                if (submitBtn && submitBtn.getAttribute('data-sec-confirm')) {
                    confirmMsg = submitBtn.getAttribute('data-sec-confirm');
                }

                var title = form.getAttribute('data-confirm-title');
                if (submitBtn && submitBtn.getAttribute('data-confirm-title')) {
                    title = submitBtn.getAttribute('data-confirm-title');
                }

                var btnText = form.getAttribute('data-confirm-btn');
                if (submitBtn && submitBtn.getAttribute('data-confirm-btn')) {
                    btnText = submitBtn.getAttribute('data-confirm-btn');
                }

                var type = form.getAttribute('data-confirm-type');
                if (submitBtn && submitBtn.getAttribute('data-confirm-type')) {
                    type = submitBtn.getAttribute('data-confirm-type');
                }

                if (!confirmMsg) {
                    var isDelete = form.querySelector('input[name="_method"][value="DELETE"]') ||
                        (submitBtn && (submitBtn.classList.contains('sec-btn-danger') || (submitBtn.textContent && submitBtn.textContent.indexOf('🗑️') !== -1)));
                    var isPatch = form.querySelector('input[name="_method"][value="PATCH"]');
                    var btnLabel = submitBtn ? submitBtn.textContent.trim().toLowerCase() : '';

                    if (isDelete) {
                        confirmMsg = {!! json_encode(__('Apakah Anda yakin ingin menghapus data ini secara permanen?')) !!};
                        title = title || {!! json_encode(__('Konfirmasi Hapus')) !!};
                        type = type || 'danger';
                        btnText = btnText || {!! json_encode(__('Ya, Hapus')) !!};
                    } else if (isPatch || btnLabel.indexOf('disable') !== -1 || btnLabel.indexOf('enable') !== -1 || btnLabel.indexOf('nonaktifkan') !== -1 || btnLabel.indexOf('aktifkan') !== -1) {
                        confirmMsg = {!! json_encode(__('Apakah Anda yakin ingin mengubah status data ini?')) !!};
                        title = title || {!! json_encode(__('Konfirmasi Pengubahan Status')) !!};
                        type = type || 'warning';
                        btnText = btnText || {!! json_encode(__('Ya, Ubah Status')) !!};
                    } else if (form.id === 'pasax-settings-form') {
                        confirmMsg = {!! json_encode(__('Simpan seluruh perubahan konfigurasi WAF ke database?')) !!};
                        title = title || {!! json_encode(__('Konfirmasi Simpan Pengaturan')) !!};
                        type = type || 'primary';
                        btnText = btnText || {!! json_encode(__('Ya, Simpan')) !!};
                    } else {
                        return;
                    }
                }

                e.preventDefault();

                showConfirm({
                    title: title || {!! json_encode(__('Konfirmasi Tindakan')) !!},
                    message: confirmMsg,
                    type: type || 'primary',
                    btnText: btnText || {!! json_encode(__('Ya, Lanjutkan')) !!}
                }, function() {
                    form.dataset.pasaxConfirmed = 'true';
                    if (submitBtn && submitBtn.name) {
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = submitBtn.name;
                        hidden.value = submitBtn.value;
                        form.appendChild(hidden);
                    }
                    if (form.requestSubmit) {
                        form.requestSubmit(submitBtn);
                    } else {
                        form.submit();
                    }
                });
            });
        })();
    </script>
    @include('pages.pasax.update-password-modal')
    @include('pages.pasax.page-support-modal')
</body>

</html>
