<?php

namespace Pasax\SecureMonitor\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Pasax\SecureMonitor\Models\PasaxTrustedIp;
use Pasax\SecureMonitor\Models\PasaxUserLogin;
use Throwable;

class PasaxUserLoginService
{
    /**
     * Record a user login with parsed device, OS, browser, and location.
     */
    public function recordLogin(
        Authenticatable $user,
        Request $request,
        bool $isDashboard = false,
    ): PasaxUserLogin {
        $securityService = app(PasaxService::class);
        $ip = $securityService->resolveClientIp($request);
        $deviceId = $securityService->resolveDeviceId($request);
        $localIp = $securityService->resolveLocalIp($request);
        $userAgent = (string) ($request->userAgent() ?? '');
        $sessionId = $request->hasSession() ? $request->session()->getId() : session()->getId();

        $clientInfo = $this->parseUserAgent($userAgent);
        $geo = $this->resolveLocation($ip);

        $deviceType = $isDashboard ? 'pasax_dashboard' : $clientInfo['device'];

        return PasaxUserLogin::create([
            'user_id' => $user->getAuthIdentifier(),
            'session_id' => $sessionId,
            'ip_address' => $ip,
            'local_ip' => $localIp,
            'device_id' => $deviceId,
            'user_agent' => $userAgent,
            'device_type' => $deviceType,
            'operating_system' => Str::limit($clientInfo['os'], 255, ''),
            'browser' => Str::limit($clientInfo['browser'], 255, ''),
            'city' => $geo['city'] ?? null,
            'region' => $geo['region'] ?? null,
            'country' => $geo['country'] ?? null,
            'country_code' => $geo['country_code'] ?? null,
            'latitude' => $geo['latitude'] ?? null,
            'longitude' => $geo['longitude'] ?? null,
            'isp' => $geo['isp'] ?? null,
            'login_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * Catat login khusus untuk pengguna yang berhasil masuk ke dashboard Pasax.
     */
    public function recordDashboardLogin(
        Authenticatable $user,
        Request $request,
    ): PasaxUserLogin {
        return $this->recordLogin($user, $request, isDashboard: true);
    }

    /**
     * Tandai atau catat sesi akses ke area dashboard Pasax.
     */
    public function trackDashboardAccess(Request $request, ?Authenticatable $user = null): void
    {
        try {
            if (! $request->hasSession()) {
                return;
            }

            $sessionId = $request->session()->getId();
            if (! $sessionId) {
                return;
            }

            if (! $user) {
                $user = $request->user();
            }

            if (! $user && $request->hasSession()) {
                $dashboardUser = $request->session()->get('pasax_dashboard_user');
                $userModel = (string) config('pasax.user_model', 'App\\Models\\User');
                if (class_exists($userModel)) {
                    if ($dashboardUser) {
                        $user = $userModel::query()
                            ->where('email', $dashboardUser)
                            ->orWhere('name', $dashboardUser)
                            ->first();
                    }
                    if (! $user) {
                        $user = $userModel::query()->first();
                    }
                }
            }

            $query = PasaxUserLogin::query();
            $existing = $query->where('session_id', $sessionId)->latest('id')->first();

            if (! $existing && $user) {
                $existing = PasaxUserLogin::query()
                    ->where('user_id', $user->getAuthIdentifier())
                    ->where('ip_address', $request->ip())
                    ->whereNull('logout_at')
                    ->latest('id')
                    ->first();
            }

            if ($existing) {
                $updates = ['last_activity_at' => now()];
                if ($existing->device_type !== 'pasax_dashboard') {
                    $updates['device_type'] = 'pasax_dashboard';
                }
                if (! $existing->session_id && $sessionId) {
                    $updates['session_id'] = $sessionId;
                }
                $existing->update($updates);

                return;
            }

            if ($user) {
                $this->recordDashboardLogin($user, $request);
            }
        } catch (Throwable) {
            // Abaikan kesalahan pencatatan agar navigasi dasbor tidak terganggu
        }
    }

    /**
     * Tandai sesi tertentu sebagai berakhir (logout) berdasarkan session id.
     *
     * @return int Jumlah baris yang diperbarui.
     */
    public function logoutSession(string $sessionId): int
    {
        return PasaxUserLogin::query()
            ->where('session_id', $sessionId)
            ->whereNull('logout_at')
            ->update(['logout_at' => now()]);
    }

    /**
     * Update last activity for an active user session.
     */
    public function updateActivity(
        int $userId,
        string $ip,
        ?string $sessionId = null,
    ): void {
        try {
            $query = PasaxUserLogin::query()
                ->where('user_id', $userId)
                ->whereNull('logout_at');

            if ($sessionId) {
                $query->where('session_id', $sessionId);
            } else {
                $query->where('ip_address', $ip);
            }

            $login = $query->latest('id')->first();

            if ($login) {
                $login->update(['last_activity_at' => now()]);
            }
        } catch (Throwable) {
            // Ignore database errors during heartbeat to preserve user experience
        }
    }

    /**
     * Parse User-Agent string to extract Operating System, Browser, and Device.
     *
     * @return array{os: string, browser: string, device: string}
     */
    public function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'os' => 'Unknown OS',
                'browser' => 'Unknown Browser',
                'device' => 'desktop',
            ];
        }

        $os = 'Unknown OS';
        $browser = 'Unknown Browser';
        $device = 'desktop';

        // 1. Device Type Detection
        if (
            preg_match(
                '/(tablet|ipad|playbook|silk)|(android(?!.*mobile))/i',
                $userAgent,
            )
        ) {
            $device = 'tablet';
        } elseif (
            preg_match(
                '/(mobile|iphone|ipod|blackberry|opera mini|iemobile|mobile.*firefox)/i',
                $userAgent,
            )
        ) {
            $device = 'mobile';
        }

        // 2. Operating System Detection
        if (preg_match("/windows nt 10\.0/i", $userAgent)) {
            $os = 'Windows 11 / 10';
        } elseif (preg_match("/windows nt 6\.3/i", $userAgent)) {
            $os = 'Windows 8.1';
        } elseif (preg_match("/windows nt 6\.2/i", $userAgent)) {
            $os = 'Windows 8';
        } elseif (preg_match("/windows nt 6\.1/i", $userAgent)) {
            $os = 'Windows 7';
        } elseif (preg_match('/windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (
            preg_match("/android\s*([0-9\.]+)?/i", $userAgent, $matches)
        ) {
            $os = 'Android'.(! empty($matches[1]) ? ' '.$matches[1] : '');
        } elseif (preg_match('/(iphone|ipad|ipod)/i', $userAgent)) {
            if (preg_match("/os\s*([0-9_]+)/i", $userAgent, $matches)) {
                $os = 'iOS '.str_replace('_', '.', $matches[1]);
            } else {
                $os = 'iOS';
            }
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            if (preg_match("/mac os x\s*([0-9_]+)/i", $userAgent, $matches)) {
                $os = 'macOS '.str_replace('_', '.', $matches[1]);
            } else {
                $os = 'macOS';
            }
        } elseif (preg_match('/cros/i', $userAgent)) {
            $os = 'Chrome OS';
        } elseif (preg_match('/ubuntu/i', $userAgent)) {
            $os = 'Ubuntu Linux';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'Linux';
        }

        // 3. Browser Detection
        if (preg_match("/edg(e)?\/([0-9\.]+)/i", $userAgent, $matches)) {
            $browser = 'Microsoft Edge '.explode('.', $matches[2])[0];
        } elseif (
            preg_match("/opr\/([0-9\.]+)/i", $userAgent, $matches) ||
            preg_match("/opera\/([0-9\.]+)/i", $userAgent, $matches)
        ) {
            $browser = 'Opera '.explode('.', $matches[1])[0];
        } elseif (
            preg_match("/samsungbrowser\/([0-9\.]+)/i", $userAgent, $matches)
        ) {
            $browser = 'Samsung Internet '.explode('.', $matches[1])[0];
        } elseif (preg_match("/chrome\/([0-9\.]+)/i", $userAgent, $matches)) {
            $browser = 'Google Chrome '.explode('.', $matches[1])[0];
        } elseif (preg_match("/firefox\/([0-9\.]+)/i", $userAgent, $matches)) {
            $browser = 'Mozilla Firefox '.explode('.', $matches[1])[0];
        } elseif (
            preg_match("/version\/([0-9\.]+).*safari/i", $userAgent, $matches)
        ) {
            $browser = 'Apple Safari '.explode('.', $matches[1])[0];
        } elseif (preg_match("/safari\/([0-9\.]+)/i", $userAgent)) {
            $browser = 'Apple Safari';
        }

        return [
            'os' => $os,
            'browser' => $browser,
            'device' => $device,
        ];
    }

    /**
     * Resolve IP Geolocation info.
     *
     * @return array{city: string|null, region: string|null, country: string|null, country_code: string|null, latitude: float|null, longitude: float|null, isp: string|null}
     */
    public function resolveLocation(string $ip): array
    {
        $isLocal =
            in_array($ip, ['127.0.0.1', '::1'], true) ||
            preg_match(
                "/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/",
                $ip,
            );

        if ($isLocal) {
            return [
                'city' => 'Pekanbaru',
                'region' => 'Riau',
                'country' => 'Indonesia',
                'country_code' => 'ID',
                'latitude' => 0.5071,
                'longitude' => 101.4478,
                'isp' => 'Jaringan Lokal / Intranet',
            ];
        }

        return Cache::remember(
            "geoip:{$ip}",
            now()->addDays(30),
            function () use ($ip) {
                try {
                    $response = Http::timeout(2)->get(
                        "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,regionName,city,lat,lon,isp",
                    );

                    if (
                        $response->successful() &&
                        $response->json('status') === 'success'
                    ) {
                        $data = $response->json();

                        return [
                            'city' => $data['city'] ?? null,
                            'region' => $data['regionName'] ?? null,
                            'country' => $data['country'] ?? null,
                            'country_code' => $data['countryCode'] ?? null,
                            'latitude' => isset($data['lat'])
                                ? (float) $data['lat']
                                : null,
                            'longitude' => isset($data['lon'])
                                ? (float) $data['lon']
                                : null,
                            'isp' => $data['isp'] ?? null,
                        ];
                    }
                } catch (Throwable) {
                    // Return generic fallback on timeout or error
                }

                return [
                    'city' => null,
                    'region' => null,
                    'country' => 'Indonesia',
                    'country_code' => 'ID',
                    'latitude' => null,
                    'longitude' => null,
                    'isp' => null,
                ];
            },
        );
    }

    /**
     * Check if an IP/device is trusted for a specific user.
     */
    public function isIpTrusted(
        string $ip,
        int $userId,
        ?string $deviceId = null,
        ?string $localIp = null,
    ): bool {
        return PasaxTrustedIp::isTrusted($ip, $userId, $deviceId, $localIp);
    }

    /**
     * Check if an IP/device is trusted across the system (used for firewall whitelisting).
     */
    public function isIpGloballyTrusted(
        string $ip,
        ?string $deviceId = null,
        ?string $localIp = null,
    ): bool {
        return PasaxTrustedIp::isAnyTrusted($ip, $deviceId, $localIp);
    }

    /**
     * Save current IP and device as trusted for the given user.
     */
    public function trustIp(
        Authenticatable $user,
        string $ip,
        ?string $deviceName = null,
        ?string $userAgent = null,
        ?string $localIp = null,
        ?string $deviceId = null,
    ): PasaxTrustedIp {
        $clientInfo = $this->parseUserAgent(
            $userAgent ?? request()?->userAgent(),
        );
        $geo = $this->resolveLocation($ip);

        $locationParts = array_filter([
            $geo['city'] ?? null,
            $geo['region'] ?? null,
            $geo['country'] ?? null,
        ]);
        $locationStr = ! empty($locationParts)
            ? implode(', ', $locationParts)
            : 'Indonesia';

        $defaultName =
            $deviceName ?: $clientInfo['os'].' - '.$clientInfo['browser'];

        $matchCriteria = [
            'user_id' => $user->getAuthIdentifier(),
            'ip_address' => $ip,
        ];

        return PasaxTrustedIp::updateOrCreate($matchCriteria, [
            'local_ip' => $localIp,
            'device_id' => $deviceId,
            'device_name' => Str::limit($defaultName, 255, ''),
            'operating_system' => Str::limit($clientInfo['os'], 255, ''),
            'browser' => Str::limit($clientInfo['browser'], 255, ''),
            'location' => Str::limit($locationStr, 255, ''),
            'is_active' => true,
            'verified_at' => now(),
        ]);
    }

    /**
     * Get real-time active users accessing the application right now.
     *
     * @return array{
     *     count: int,
     *     is_multi_user: bool,
     *     users: array<int, array<string, mixed>>
     * }
     */
    public function getRealtimeActiveUsers(): array
    {
        $fiveMinutesAgo = now()->subMinutes(5)->getTimestamp();

        // 1. Fetch active session entries from sessions table
        $activeSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $fiveMinutesAgo)
            ->get();

        $userIds = $activeSessions->pluck('user_id')->unique()->values()->all();

        // 2. Fetch corresponding users (model user dikonfigurasi secara dinamis)
        /** @var class-string<Model> $userModel */
        $userModel = (string) config(
            'pasax.user_model',
            'App\\Models\\User',
        );
        $users = $userModel::query()
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        $activeList = [];

        foreach ($activeSessions as $session) {
            $user = $users->get($session->user_id);
            if (! $user) {
                continue;
            }

            $clientInfo = $this->parseUserAgent($session->user_agent);
            $lastActivity = Carbon::createFromTimestamp(
                $session->last_activity,
            );

            $activeList[] = [
                'session_id' => $session->id,
                'user_id' => $user->getAttribute('id'),
                'name' => $user->getAttribute('name'),
                'email' => $user->getAttribute('email'),
                'role' => $user->getAttribute('role'),
                'ip_address' => $session->ip_address,
                'operating_system' => $clientInfo['os'],
                'browser' => $clientInfo['browser'],
                'device_type' => $clientInfo['device'],
                'last_activity' => $lastActivity->toIso8601String(),
                'last_activity_human' => $lastActivity->diffForHumans(),
            ];
        }

        $uniqueUserCount = count($userIds);

        return [
            'count' => $uniqueUserCount,
            'is_multi_user' => $uniqueUserCount >= 2,
            'users' => $activeList,
        ];
    }
}
