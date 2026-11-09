<?php

namespace Internal\Pasak\Support;

use Internal\Pasak\Services\PasakUserLoginService;

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
        } catch (\Throwable) {
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
}
