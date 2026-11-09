<?php

namespace Internal\Pasak\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Internal\Pasak\Models\PasakBlockedIp;
use Internal\Pasak\Models\PasakLog;
use Internal\Pasak\Services\PasakService;
use Internal\Pasak\Services\PasakUserLoginService;

/**
 * Threat map page for the Blade monitoring dashboard.
 *
 * Menggambar lintasan serangan dari lokasi geografis penyerang menuju
 * lokasi server yang dipantau. Lokasi IP memakai resolver geoIP berikut
 * cache 30 hari yang sama dengan pelacak sesi.
 */
class PasakThreatMapController extends Controller
{
    use RespondsWithPasakDashboard;

    public function __construct(
        protected PasakUserLoginService $loginService,
    ) {}

    public function index(Request $request, PasakService $security): View
    {
        $window = $this->windowHours();
        $start = now()->subHours($window);

        // Ambil IP penyerang terbanyak dalam rentang waktu window_hours
        $topRows = PasakLog::query()
            ->selectRaw(
                'ip_address, COUNT(*) as hits, MAX(created_at) as last_seen, '
                ."MAX(case threat_level when 'critical' then 4 when 'high' then 3 when 'medium' then 2 else 1 end) as weight"
            )
            ->where('created_at', '>=', $start)
            ->groupBy('ip_address')
            ->orderByDesc('hits')
            ->limit($this->maxOrigins())
            ->get();

        // Jika jumlah origin dalam rentang window kurang dari maxOrigins, lengkapi dengan riwayat log sebelumnya
        // agar seluruh data serangan riil yang ada di Security Logs (logs.blade.php) tetap tampil di peta
        if ($topRows->count() < $this->maxOrigins()) {
            $existingIps = $topRows->pluck('ip_address')->all();
            $fallbackRows = PasakLog::query()
                ->selectRaw(
                    'ip_address, COUNT(*) as hits, MAX(created_at) as last_seen, '
                    ."MAX(case threat_level when 'critical' then 4 when 'high' then 3 when 'medium' then 2 else 1 end) as weight"
                )
                ->when(! empty($existingIps), fn ($q) => $q->whereNotIn('ip_address', $existingIps))
                ->groupBy('ip_address')
                ->orderByDesc('hits')
                ->limit($this->maxOrigins() - $topRows->count())
                ->get();

            $topRows = $topRows->concat($fallbackRows);
        }

        $activeBlocked = [];
        try {
            $originIps = $topRows->pluck('ip_address')->unique()->filter()->all();
            if (! empty($originIps)) {
                $activeBlocked = PasakBlockedIp::query()
                    ->active()
                    ->whereIn('ip_address', $originIps)
                    ->pluck('ip_address')
                    ->flip()
                    ->all();
            }
        } catch (\Throwable) {
        }

        $origins = $topRows
            ->map(function ($row) use ($activeBlocked): array {
                $geo = $this->locationFor((string) $row->ip_address);
                $sample = PasakLog::query()
                    ->where('ip_address', $row->ip_address)
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get();

                return [
                    'ip' => (string) $row->ip_address,
                    'lat' => $geo['latitude'],
                    'lon' => $geo['longitude'],
                    'place' => $geo['city'],
                    'country' => $geo['country'],
                    'hits' => (int) $row->hits,
                    'weight' => (int) $row->weight,
                    'last_seen' => (string) $row->last_seen,
                    'samples' => $sample
                        ->map(fn (PasakLog $log): array => $this->logToArray($log, true, $activeBlocked))
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        // Ambil riwayat log terbaru (persis sama dengan entri pada halaman Security Logs)
        $recentLogs = PasakLog::query()
            ->latest('id')
            ->limit(50)
            ->get();

        $recentLogIps = $recentLogs->pluck('ip_address')->unique()->filter()->all();
        $recentLocations = [];
        foreach ($recentLogIps as $rIp) {
            $rGeo = $this->locationFor((string) $rIp);
            $recentLocations[$rIp] = [
                'lat' => $rGeo['latitude'],
                'lon' => $rGeo['longitude'],
                'place' => $rGeo['city'],
                'country' => $rGeo['country'],
            ];
        }

        return $this->dashboardResponse('pages.pasak.threat-map', [
            'payload' => [
                'target' => $this->target(),
                'window' => $window,
                'refresh' => $this->refreshSeconds(),
                'enforcement' => $security->enforcementEnabled(),
                'origins' => $origins,
                'recent_logs' => $recentLogs
                    ->map(fn (PasakLog $log): array => $this->logToArray($log, true, $activeBlocked))
                    ->values()
                    ->all(),
                'locations' => $recentLocations,
                'feedUrl' => route('pasak.dashboard.threat-map.feed'),
            ],
            'stats' => $security->stats(),
        ]);
    }

    public function feed(Request $request, PasakService $security): JsonResponse
    {
        $after = (int) $request->query('after', 0);

        $query = PasakLog::query()
            ->orderByDesc('id');

        if ($after > 0) {
            $query->where('id', '>', $after);
        } else {
            $query->limit(20);
        }

        $logs = $query->limit(20)->get();

        $locations = [];

        foreach ($logs->unique('ip_address') as $log) {
            $ip = (string) $log->ip_address;
            $geo = $this->locationFor($ip);
            $locations[$ip] = [
                'lat' => $geo['latitude'],
                'lon' => $geo['longitude'],
                'place' => $geo['city'],
                'country' => $geo['country'],
            ];
        }

        $activeBlocked = [];
        try {
            $uniqueIps = $logs->pluck('ip_address')->unique()->filter()->all();
            if (! empty($uniqueIps)) {
                $activeBlocked = PasakBlockedIp::query()
                    ->active()
                    ->whereIn('ip_address', $uniqueIps)
                    ->pluck('ip_address')
                    ->flip()
                    ->all();
            }
        } catch (\Throwable) {
        }

        return response()->json([
            'last_id' => (int) ($logs->first()->id ?? $after),
            'stats' => $security->stats(),
            'locations' => $locations,
            'logs' => $logs
                ->map(fn (PasakLog $log): array => $this->logToArray($log, true, $activeBlocked))
                ->values()
                ->all(),
        ]);
    }

    protected function logToArray(PasakLog $log, bool $withTime = false, array $activeBlocked = []): array
    {
        $isBlocked = (bool) $log->was_blocked || isset($activeBlocked[$log->ip_address]);

        return [
            'id' => $log->id,
            'ip' => (string) $log->ip_address,
            'level' => (string) $log->threat_level,
            'event' => (string) $log->event_type,
            'desc' => (string) ($log->rule_label ?: $log->event_type),
            'method' => $log->method ? (string) $log->method : null,
            'path' => mb_substr((string) $log->path, 0, 140),
            'blocked' => $isBlocked,
            'time' => $withTime
                ? $log->created_at?->toIso8601String()
                : null,
        ];
    }

    /**
     * @return array{name: string, lat: float, lon: float}
     */
    protected function target(): array
    {
        $manualName = config('pasak.dashboard.threat_map.target_name');
        $manualLat = config('pasak.dashboard.threat_map.target_lat');
        $manualLon = config('pasak.dashboard.threat_map.target_lon');

        if (! empty($manualName) && $manualLat !== null && $manualLon !== null) {
            return [
                'name' => (string) $manualName,
                'lat' => (float) $manualLat,
                'lon' => (float) $manualLon,
            ];
        }

        if (config('pasak.dashboard.threat_map.auto_detect_target', true)) {
            $detected = $this->autoDetectServerTarget();
            if ($detected !== null) {
                if (! empty($manualName)) {
                    $detected['name'] = (string) $manualName;
                }
                if ($manualLat !== null) {
                    $detected['lat'] = (float) $manualLat;
                }
                if ($manualLon !== null) {
                    $detected['lon'] = (float) $manualLon;
                }

                return $detected;
            }
        }

        return [
            'name' => ! empty($manualName) ? (string) $manualName : 'Pekanbaru, Riau (ID)',
            'lat' => $manualLat !== null ? (float) $manualLat : 0.5071,
            'lon' => $manualLon !== null ? (float) $manualLon : 101.4478,
        ];
    }

    /**
     * Auto-detect lokasi server berdasarkan IP publik server pemanggil.
     * Hasil disimpan dalam cache selama 7 hari.
     *
     * @return array{name: string, lat: float, lon: float}|null
     */
    protected function autoDetectServerTarget(): ?array
    {
        return Cache::remember('pasak:threat_map_server_target', now()->addDays(7), function (): ?array {
            try {
                $response = Http::timeout(3)->get(
                    'http://ip-api.com/json?fields=status,message,country,countryCode,regionName,city,lat,lon'
                );

                if ($response->successful() && $response->json('status') === 'success') {
                    $data = $response->json();
                    $lat = isset($data['lat']) ? (float) $data['lat'] : null;
                    $lon = isset($data['lon']) ? (float) $data['lon'] : null;

                    if ($lat !== null && $lon !== null) {
                        $city = $data['city'] ?? $data['regionName'] ?? 'Server Node';
                        $region = $data['regionName'] ?? '';
                        $countryCode = $data['countryCode'] ?? 'ID';

                        $labelParts = array_filter([$city, $region]);
                        $name = ! empty($labelParts)
                            ? implode(', ', array_unique($labelParts)) . " ({$countryCode})"
                            : "Server Node ({$countryCode})";

                        return [
                            'name' => $name,
                            'lat' => $lat,
                            'lon' => $lon,
                        ];
                    }
                }
            } catch (\Throwable) {
            }

            return null;
        });
    }

    /**
     * @return array{city: string|null, country: string|null, latitude: float|null, longitude: float|null}
     */
    protected function locationFor(string $ip): array
    {
        $geo = $this->loginService->resolveLocation($ip);

        return [
            'city' => $geo['city'] ?? null,
            'country' => $geo['country'] ?? null,
            'latitude' => $geo['latitude'] ?? null,
            'longitude' => $geo['longitude'] ?? null,
        ];
    }

    protected function windowHours(): int
    {
        return max(1, (int) config('pasak.dashboard.threat_map.window_hours', 24));
    }

    protected function maxOrigins(): int
    {
        return max(1, (int) config('pasak.dashboard.threat_map.max_origins', 10));
    }

    protected function refreshSeconds(): int
    {
        return max(3, (int) config('pasak.dashboard.threat_map.refresh_seconds', 8));
    }
}
