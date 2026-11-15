<?php

namespace Pasax\SecureMonitor\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Pasax\SecureMonitor\Models\PasaxTrustedIp;
use Pasax\SecureMonitor\Models\PasaxUserLogin;
use Pasax\SecureMonitor\Services\PasaxService;
use Pasax\SecureMonitor\Services\PasaxUserLoginService;

/**
 * User session tracking page for the Blade monitoring dashboard.
 */
class PasaxSessionController extends Controller
{
    use RespondsWithPasaxDashboard;

    public function index(Request $request): View
    {
        $currentUser = $this->resolveCurrentUser($request);
        if ($currentUser) {
            try {
                app(PasaxUserLoginService::class)->trackDashboardAccess($request, $currentUser);
            } catch (\Throwable) {
            }
        }

        $query = PasaxUserLogin::query()->with('user')->dashboard()->latest('id');

        $search = (string) $request->query('search', '');
        $dateFrom = (string) $request->query('date_from', '');
        $dateTo = (string) $request->query('date_to', '');
        $perPage = (int) $request->query('per_page', 25);

        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 25;
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhere('browser', 'like', "%{$search}%")
                    ->orWhere('operating_system', 'like', "%{$search}%")
                    ->orWhere('device_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search): void {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom !== '') {
            try {
                $from = Carbon::parse($dateFrom)->startOfDay();
                $query->where(function ($q) use ($from): void {
                    $q->where('login_at', '>=', $from)
                        ->orWhere('created_at', '>=', $from);
                });
            } catch (\Throwable) {
            }
        }

        if ($dateTo !== '') {
            try {
                $to = Carbon::parse($dateTo)->endOfDay();
                $query->where(function ($q) use ($to): void {
                    $q->where('login_at', '<=', $to)
                        ->orWhere('created_at', '<=', $to);
                });
            } catch (\Throwable) {
            }
        }

        $currentIp = app(PasaxService::class)->resolveClientIp($request);
        $currentTrusted = PasaxTrustedIp::query()
            ->where('ip_address', $currentIp)
            ->where('is_active', true)
            ->when($currentUser, function ($q) use ($currentUser) {
                $q->where('user_id', $currentUser->getKey());
            })
            ->first();

        return $this->dashboardResponse(
            'pages.pasax.sessions',
            [
                'logins' => $query->paginate($perPage)->withQueryString(),
                'trustedIps' => PasaxTrustedIp::query()->with('user')->latest('id')->get(),
                'onlineCount' => PasaxUserLogin::query()->dashboard()->active(5)->count(),
                'currentUser' => $currentUser,
                'currentIp' => $currentIp,
                'currentTrusted' => $currentTrusted,
                'isCurrentIpTrusted' => (bool) $currentTrusted,
                'filters' => [
                    'search' => $search,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'per_page' => $perPage,
                ],
                'urls' => [
                    'index' => route('pasax.dashboard.sessions'),
                    'loginDestroy' => route('pasax.dashboard.user-sessions.destroy', ['id' => '__ID__']),
                    'sessionDestroy' => route('pasax.dashboard.sessions.destroy', ['sessionId' => '__SESSION__']),
                    'trustedDestroy' => route('pasax.dashboard.trusted-ips.destroy', ['id' => '__ID__']),
                    'storeMyIp' => route('pasax.dashboard.trusted-ips.store-my-ip'),
                ],
            ]
        );
    }

    public function destroyLogin(int|string $id): RedirectResponse
    {
        PasaxUserLogin::query()->whereKey($id)->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('Login history entry deleted.'),
        ]);
    }

    public function destroySession(string $sessionId, PasaxUserLoginService $logins): RedirectResponse
    {
        $logins->logoutSession($sessionId);

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('Session terminated.'),
        ]);
    }

    public function destroyTrusted(int|string $id): RedirectResponse
    {
        $trusted = PasaxTrustedIp::query()->find($id);
        $ip = $trusted?->ip_address;

        $trusted?->delete();

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('Trusted IP :ip removed.', ['ip' => $ip ?? '']),
        ]);
    }

    public function storeMyIp(
        Request $request,
        PasaxService $security,
        PasaxUserLoginService $loginService,
    ): RedirectResponse {
        $user = $this->resolveCurrentUser($request);
        $isDashboardAuth = $request->hasSession() && $request->session()->get('pasax_dashboard_authenticated') === true;

        if (! $user && ! $isDashboardAuth) {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => __('Anda harus login untuk menyimpan IP terpercaya.'),
            ]);
        }

        if (! $user) {
            return back()->with('toast', [
                'type' => 'danger',
                'message' => __('Tidak ditemukan akun pengguna untuk mengaitkan IP terpercaya.'),
            ]);
        }

        $ip = $security->resolveClientIp($request);
        $userAgent = (string) ($request->userAgent() ?? '');
        $deviceId = $security->resolveDeviceId($request);
        $localIp = $security->resolveLocalIp($request);

        $loginService->trustIp(
            user: $user,
            ip: $ip,
            deviceName: null,
            userAgent: $userAgent,
            localIp: $localIp,
            deviceId: $deviceId,
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => __('IP :ip saved as trusted.', ['ip' => $ip]),
        ]);
    }

    /**
     * Resolusi pengguna aktif, memprioritaskan auth guard Laravel atau akun sesi dashboard Pasax.
     */
    protected function resolveCurrentUser(Request $request): mixed
    {
        $user = $request->user();
        if ($user) {
            return $user;
        }

        $userModel = (string) config('pasax.user_model', 'App\\Models\\User');
        if (class_exists($userModel)) {
            $dashboardUser = session('pasax_dashboard_user');
            if ($dashboardUser) {
                $found = $userModel::query()
                    ->where('email', $dashboardUser)
                    ->orWhere('name', $dashboardUser)
                    ->first();
                if ($found) {
                    return $found;
                }
            }

            return $userModel::query()->first();
        }

        return null;
    }
}
