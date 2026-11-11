<?php

namespace Internal\Pasak\Support;

use DateTimeInterface;
use DateTimeZone;
use Illuminate\Support\Carbon;
use Internal\Pasak\Services\PasakUserLoginService;
use Throwable;

/**
 * Helper presentasi bersama untuk dashboard Blade murni (tanpa Livewire).
 */
class PasakDashboardView
{
    public static function levelLabel(string $level): string
    {
        return match ($level) {
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            default => 'Low',
        };
    }

    public static function levelClass(string $level): string
    {
        return match ($level) {
            'critical' => 'sec-badge-critical',
            'high' => 'sec-badge-high',
            'medium' => 'sec-badge-medium',
            default => 'sec-badge-low',
        };
    }

    public static function trendBarHeight(mixed $total, int $maxTrend): string
    {
        return max(4, (int) round(((int) $total / max(1, $maxTrend)) * 120)).'px';
    }

    public static function severityPercent(int $count, int $levelTotal): string
    {
        return (int) round(($count / max(1, $levelTotal)) * 100).'%';
    }

    public static function severityColor(string $level): string
    {
        return match ($level) {
            'critical' => 'var(--sec-danger)',
            'high' => 'var(--sec-warning)',
            'medium' => '#d97706',
            default => 'var(--sec-text-subtle)',
        };
    }

    public static function statusBadgeClass(?string $status): string
    {
        return match ($status) {
            'critical' => 'sec-badge-critical',
            'warning' => 'sec-badge-high',
            'ok' => 'sec-badge-success',
            default => 'sec-badge-low',
        };
    }

    /**
     * Konversi kode negara 2 huruf ISO ke bendera emoji.
     */
    public static function countryFlag(?string $countryCode): string
    {
        if (empty($countryCode) || strlen($countryCode) !== 2 || ! ctype_alpha($countryCode)) {
            return '🌐';
        }

        $code = strtoupper($countryCode);
        $first = ord($code[0]) - ord('A') + 0x1F1E6;
        $second = ord($code[1]) - ord('A') + 0x1F1E6;

        return mb_chr($first, 'UTF-8').mb_chr($second, 'UTF-8');
    }

    /**
     * Resolusi lokasi IP (negara, provinsi, kota, ISP) dengan fallback aman.
     *
     * @return array{city: string|null, region: string|null, country: string|null, country_code: string|null, latitude: float|null, longitude: float|null, isp: string|null}
     */
    public static function resolveLocation(string $ip): array
    {
        try {
            return app(PasakUserLoginService::class)->resolveLocation($ip);
        } catch (Throwable) {
            return [
                'city' => null,
                'region' => null,
                'country' => null,
                'country_code' => null,
                'latitude' => null,
                'longitude' => null,
                'isp' => null,
            ];
        }
    }

    /**
     * Resolusi timezone tampilan dasbor:
     * 1. Prioritas konfigurasi eksplisit: config('pasak.dashboard.timezone') atau env('PASAK_DASHBOARD_TIMEZONE')
     * 2. env('APP_TIMEZONE') dari .env aplikasi host
     * 3. config('app.timezone') jika bukan 'UTC' default bawaan Laravel
     * 4. Auto-detect timezone server fisik tempat aplikasi dipasang (/etc/localtime atau /etc/timezone)
     * 5. Fallback ke config('app.timezone', 'UTC')
     */
    public static function timezone(): string
    {
        $custom = config('pasak.dashboard.timezone') ?: env('PASAK_DASHBOARD_TIMEZONE') ?: env('APP_TIMEZONE');
        if (! empty($custom) && in_array($custom, DateTimeZone::listIdentifiers(), true)) {
            return (string) $custom;
        }

        $appTz = config('app.timezone');
        if (! empty($appTz) && $appTz !== 'UTC' && in_array($appTz, DateTimeZone::listIdentifiers(), true)) {
            return (string) $appTz;
        }

        // Auto-detect timezone sistem/server tempat aplikasi diinstal
        $detected = null;
        if (is_link('/etc/localtime')) {
            $link = @readlink('/etc/localtime');
            if ($link && preg_match('#(?:zoneinfo|zoneinfo\.default)/(.+)$#', $link, $matches)) {
                $detected = $matches[1];
            }
        }
        if (! $detected && file_exists('/etc/timezone') && is_readable('/etc/timezone')) {
            $t = trim((string) @file_get_contents('/etc/timezone'));
            if ($t !== '') {
                $detected = $t;
            }
        }

        if ($detected && in_array($detected, DateTimeZone::listIdentifiers(), true)) {
            return (string) $detected;
        }

        return ! empty($appTz) ? (string) $appTz : 'UTC';
    }

    /**
     * Format tanggal/waktu sesuai locale aplikasi (APP_LOCALE=id / en) dan timezone server pengguna.
     */
    public static function formatDate(mixed $date, string $format = 'd M Y H:i:s'): string
    {
        if (! $date) {
            return '—';
        }

        if (! ($date instanceof DateTimeInterface)) {
            try {
                $date = Carbon::parse($date);
            } catch (Throwable) {
                return (string) $date;
            }
        }

        $carbon = $date instanceof Carbon
            ? $date->copy()
            : Carbon::instance($date);

        $locale = app()->getLocale();
        if (in_array($locale, ['id', 'en'], true)) {
            $carbon->locale($locale);
        }

        try {
            $carbon->setTimezone(static::timezone());
        } catch (Throwable) {
            // Abaikan jika timezone tidak valid
        }

        return $carbon->translatedFormat($format);
    }

    /**
     * Tampilan waktu relatif (misal: "5 menit yang lalu" atau "5 minutes ago") sesuai locale dan timezone server.
     */
    public static function diffForHumans(mixed $date, bool $syntax = false): string
    {
        if (! $date) {
            return '—';
        }

        if (! ($date instanceof DateTimeInterface)) {
            try {
                $date = Carbon::parse($date);
            } catch (Throwable) {
                return (string) $date;
            }
        }

        $carbon = $date instanceof Carbon
            ? $date->copy()
            : Carbon::instance($date);

        $locale = app()->getLocale();
        if (in_array($locale, ['id', 'en'], true)) {
            $carbon->locale($locale);
        }

        try {
            $carbon->setTimezone(static::timezone());
        } catch (Throwable) {
            // Abaikan jika timezone tidak valid
        }

        return $carbon->diffForHumans(null, $syntax);
    }
}
