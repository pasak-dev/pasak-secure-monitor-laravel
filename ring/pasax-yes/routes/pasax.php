<?php

use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxAllowedIpController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxAuthController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxBlockedIpController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxDashboardAliasController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxDashboardEntryController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxLogController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxOverviewController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxServerController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxSessionController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxSettingController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxThreatMapController;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\PasaxTicketController;
use Pasax\SecureMonitor\Http\Middleware\PasaxDashboardAuthenticate;
use Pasax\SecureMonitor\Http\Middleware\PasaxRotatingDashboard;

/*
|--------------------------------------------------------------------------
| Pasax Web Dashboard Routes (Plain Blade)
|--------------------------------------------------------------------------
|
| Rute dashboard Blade murni — satu-satunya dashboard Pasax (100% Pure PHP).
| Me-render tampilan di resources/views/pages/pasax tanpa dependensi
| frontend sehingga Anda bebas menyesuaikan tampilan, prefix URL,
| middleware, atau menambah halaman sendiri.
|
| Dilindungi oleh middleware autentikasi mandiri Pasax:
| - Username: PASAX_DASHBOARD_USERNAME (default: admin)
| - Password: PASAX_DASHBOARD_SECRET (secret password server)
|
| Mode URL acak sekali pakai (PASAX_DASHBOARD_ROTATING=true):
| pintu stabil "/pasax" 302 ke "/pasax-{token}/" milik sesi pengguna.
| Token diputar setiap akses HTML sehingga reload selalu berubah,
| mis. /pasax-95856857476jdhjs5/logs lalu /pasax-634664873647jbdjshf/logs.
|
*/

$prefix = trim((string) config('pasax.dashboard.prefix', 'pasax'), '/');
if ($prefix === '') {
    $prefix = 'pasax';
}

$configuredMiddleware = array_filter(
    (array) config('pasax.dashboard.middleware', ['web', 'auth']),
);
$authEnabled = (bool) config('pasax.dashboard.auth.enabled', true);

$middleware = [];
foreach ($configuredMiddleware as $m) {
    if ($m === 'auth' && $authEnabled) {
        $middleware[] = PasaxDashboardAuthenticate::class;
    } else {
        $middleware[] = $m;
    }
}

if ($authEnabled && ! in_array(PasaxDashboardAuthenticate::class, $middleware, true)) {
    $middleware[] = PasaxDashboardAuthenticate::class;
}

$middleware = array_values(array_unique(array_filter($middleware)));

$adminMiddleware = array_filter(
    (array) config('pasax.dashboard.admin_middleware', []),
);
$rotating = (bool) config('pasax.dashboard.rotating', false);

// Rute Form Login & Logout Mandiri Dashboard Pasax (tanpa proteksi auth)
Route::middleware(['web'])
    ->prefix($prefix)
    ->group(function () {
        Route::get('login', [PasaxAuthController::class, 'showLoginForm'])
            ->name('pasax.dashboard.login');
        Route::post('login', [PasaxAuthController::class, 'login'])
            ->name('pasax.dashboard.login.submit');
        Route::post('logout', [PasaxAuthController::class, 'logout'])
            ->name('pasax.dashboard.logout');
        Route::post('password', [PasaxAuthController::class, 'updatePassword'])
            ->name('pasax.dashboard.password.update');
    });

$registerPasaxDashboard = function () {
    Route::get('/', PasaxOverviewController::class)->name('overview');
    Route::get('threat-map', [PasaxThreatMapController::class, 'index'])->name('threat-map');
    Route::get('threat-map/feed', [PasaxThreatMapController::class, 'feed'])->name('threat-map.feed');
    Route::get('logs', [PasaxLogController::class, 'index'])->name('logs');
    Route::get('logs/{id}', [PasaxLogController::class, 'show'])->whereNumber('id')->name('logs.show');
    Route::delete('logs', [PasaxLogController::class, 'clear'])->name('logs.clear');
    Route::delete('logs/{id}', [PasaxLogController::class, 'destroy'])->whereNumber('id')->name('logs.destroy');
    Route::get('blocked-ips', [PasaxBlockedIpController::class, 'index'])->name('blocked-ips');
    Route::get('blocked-ips/{id}', [PasaxBlockedIpController::class, 'show'])->whereNumber('id')->name('blocked-ips.show');
    Route::post('blocked-ips', [PasaxBlockedIpController::class, 'store'])->name('blocked-ips.store');
    Route::patch('blocked-ips/{id}/toggle', [PasaxBlockedIpController::class, 'toggle'])->whereNumber('id')->name('blocked-ips.toggle');
    Route::delete('blocked-ips/{id}', [PasaxBlockedIpController::class, 'destroy'])->whereNumber('id')->name('blocked-ips.destroy');
    Route::get('allowed-ips', [PasaxAllowedIpController::class, 'index'])->name('allowed-ips');
    Route::post('allowed-ips', [PasaxAllowedIpController::class, 'store'])->name('allowed-ips.store');
    Route::patch('allowed-ips/{id}/toggle', [PasaxAllowedIpController::class, 'toggle'])->whereNumber('id')->name('allowed-ips.toggle');
    Route::delete('allowed-ips/{id}', [PasaxAllowedIpController::class, 'destroy'])->whereNumber('id')->name('allowed-ips.destroy');
    Route::get('server', [PasaxServerController::class, 'index'])->name('server');
    Route::post('server', [PasaxServerController::class, 'updateLocation']);
    Route::post('server/baseline', [PasaxServerController::class, 'createBaseline'])->name('server.baseline');
    Route::delete('server/baseline', [PasaxServerController::class, 'deleteBaseline'])->name('server.baseline.destroy');
    Route::delete('server/suspicious-files', [PasaxServerController::class, 'deleteSuspiciousFile'])->name('server.suspicious-files.destroy');
    Route::get('server/suspicious-files/download', [PasaxServerController::class, 'downloadSuspiciousFile'])->name('server.suspicious-files.download');
    Route::post('server/suspicious-files/verify', [PasaxServerController::class, 'verifySuspiciousFile'])->name('server.suspicious-files.verify');
    Route::post('server/suspicious-files/unverify', [PasaxServerController::class, 'unverifySuspiciousFile'])->name('server.suspicious-files.unverify');
    Route::post('server/location', [PasaxServerController::class, 'updateLocation'])->name('server.location');
    Route::get('server/report/export', [PasaxServerController::class, 'exportReport'])->name('server.report.export');
    Route::get('reports', [PasaxServerController::class, 'reportPage'])->name('reports');
    Route::post('optimize-clear', [PasaxServerController::class, 'optimizeClear'])->name('optimize-clear');
    Route::delete('lockouts/{id}', [PasaxServerController::class, 'releaseLockout'])->whereNumber('id')->name('lockouts.destroy');
    Route::get('sessions', [PasaxSessionController::class, 'index'])->name('sessions');
    Route::delete('user-sessions/{id}', [PasaxSessionController::class, 'destroyLogin'])->whereNumber('id')->name('user-sessions.destroy');
    Route::delete('sessions/{sessionId}', [PasaxSessionController::class, 'destroySession'])->name('sessions.destroy');
    Route::delete('trusted-ips/{id}', [PasaxSessionController::class, 'destroyTrusted'])->whereNumber('id')->name('trusted-ips.destroy');
    Route::post('trusted-ips/my-ip', [PasaxSessionController::class, 'storeMyIp'])->name('trusted-ips.store-my-ip');
    Route::get('tickets', [PasaxTicketController::class, 'index'])->name('tickets');
    Route::get('tickets/{id}', [PasaxTicketController::class, 'show'])->whereNumber('id')->name('tickets.show');
    Route::post('tickets/{id}/respond', [PasaxTicketController::class, 'respond'])->whereNumber('id')->name('tickets.respond');
    Route::delete('tickets/{id}', [PasaxTicketController::class, 'destroy'])->whereNumber('id')->name('tickets.destroy');
    Route::get('settings', [PasaxSettingController::class, 'index'])->name('settings');
    Route::post('settings', [PasaxSettingController::class, 'update'])->name('settings.update');
};

if ($rotating) {
    // Pintu stabil untuk bookmark: /pasax → 302 → /pasax-{token}/
    Route::middleware(array_merge($middleware, $adminMiddleware))
        ->get($prefix, PasaxDashboardEntryController::class)
        ->name('pasax.dashboard.entry');

    Route::middleware(
        array_merge($middleware, $adminMiddleware, [
            PasaxRotatingDashboard::class,
        ]),
    )
        ->prefix('{pasaxSlug}')
        ->where([
            'pasaxSlug' => preg_quote($prefix, '/').'-[A-Za-z0-9]{12,32}',
        ])
        ->name('pasax.dashboard.')
        ->group($registerPasaxDashboard);

    // Alias stabil: /pasax/logs → 302 → /pasax-{token}/logs. Menjaga bookmark,
    // link layout host, dan route() di luar dashboard tetap hidup.
    Route::middleware(array_merge($middleware, $adminMiddleware))
        ->prefix($prefix)
        ->group(function () {
            Route::get('{any}', PasaxDashboardAliasController::class)->where(
                'any',
                '^(?!login|logout|password).*$',
            );
        });
} else {
    Route::middleware(array_merge($middleware, $adminMiddleware))
        ->prefix($prefix)
        ->name('pasax.dashboard.')
        ->group($registerPasaxDashboard);
}
