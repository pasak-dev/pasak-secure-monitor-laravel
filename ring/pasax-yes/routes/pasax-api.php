<?php

use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxAllowedIpApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxBlockedIpApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxIpUnblockRequestApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxSecurityLogApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxSecuritySettingApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxServerSecurityApiController;
use Pasax\SecureMonitor\Http\Controllers\Api\PasaxUserSessionApiController;

/*
|--------------------------------------------------------------------------
| Security Monitor Headless REST API Routes
|--------------------------------------------------------------------------
|
| Rute REST API headless untuk Laravel Security Monitor. Anda bebas mengubah
| prefix, middleware, nama rute, atau menambahkan custom endpoint sesuai kebutuhan.
|
*/

// Jika rute ini dimuat dari dalam routes/api.php (grup 'api' sudah aktif),
// sesuaikan prefix agar URL tetap konsisten '/api/security/*'.
$rawPrefix = (string) config('pasax.routes.prefix', 'api/pasax');
$inApiGroup = Route::hasGroupStack() && collect(Route::getGroupStack())->contains(fn ($g) => ($g['prefix'] ?? '') === 'api');
$prefix = $inApiGroup ? (preg_replace('#^/?api/?#', '', $rawPrefix) ?: 'security') : $rawPrefix;
$middleware = (array) config('pasax.routes.middleware', ['api']);
$authMiddleware = (array) config('pasax.routes.auth_middleware', ['auth']);
$adminMiddleware = (array) config('pasax.routes.admin_middleware', [
    'Pasax\SecureMonitor\Http\Middleware\PasaxEnsureSecurityAdmin',
]);

Route::prefix($prefix)->middleware($middleware)->name('pasax.')->group(function () use ($authMiddleware, $adminMiddleware) {
    // Public Endpoints (Permohonan Banding & Cek Status Tiket)
    Route::post('unblock-tickets/submit', [PasaxIpUnblockRequestApiController::class, 'submit'])->name('unblock-tickets.submit');
    Route::get('unblock-tickets/check/{ticketNumber}', [PasaxIpUnblockRequestApiController::class, 'checkStatus'])->name('unblock-tickets.check');

    // Authenticated User Endpoints
    Route::middleware($authMiddleware)->group(function () {
        Route::post('trusted-ips/save-my-ip', [PasaxUserSessionApiController::class, 'storeMyIp'])->name('trusted-ips.store-my-ip');
    });

    // Admin Protected Endpoints
    Route::middleware(array_merge($authMiddleware, $adminMiddleware))->group(function () {
        // Logs
        Route::get('logs', [PasaxSecurityLogApiController::class, 'index'])->name('logs.index');
        Route::delete('logs/clear', [PasaxSecurityLogApiController::class, 'clear'])->name('logs.clear');
        Route::delete('logs/{id}', [PasaxSecurityLogApiController::class, 'destroy'])->name('logs.destroy');

        // Blocked IPs
        Route::get('blocked-ips', [PasaxBlockedIpApiController::class, 'index'])->name('blocked-ips.index');
        Route::post('blocked-ips', [PasaxBlockedIpApiController::class, 'store'])->name('blocked-ips.store');
        Route::get('blocked-ips/{id}', [PasaxBlockedIpApiController::class, 'show'])->name('blocked-ips.show');
        Route::patch('blocked-ips/{id}/toggle', [PasaxBlockedIpApiController::class, 'toggle'])->name('blocked-ips.toggle');
        Route::delete('blocked-ips/{id}', [PasaxBlockedIpApiController::class, 'destroy'])->name('blocked-ips.destroy');

        // Allowed IPs (global whitelist)
        Route::get('allowed-ips', [PasaxAllowedIpApiController::class, 'index'])->name('allowed-ips.index');
        Route::post('allowed-ips', [PasaxAllowedIpApiController::class, 'store'])->name('allowed-ips.store');
        Route::get('allowed-ips/{id}', [PasaxAllowedIpApiController::class, 'show'])->name('allowed-ips.show');
        Route::patch('allowed-ips/{id}/toggle', [PasaxAllowedIpApiController::class, 'toggle'])->name('allowed-ips.toggle');
        Route::delete('allowed-ips/{id}', [PasaxAllowedIpApiController::class, 'destroy'])->name('allowed-ips.destroy');

        // Server Security & Audit
        Route::get('server', [PasaxServerSecurityApiController::class, 'index'])->name('server.index');
        Route::post('server/baseline', [PasaxServerSecurityApiController::class, 'storeBaseline'])->name('server.baseline');
        Route::delete('server/baseline', [PasaxServerSecurityApiController::class, 'destroyBaseline'])->name('server.baseline.destroy');
        Route::delete('server/suspicious-files', [PasaxServerSecurityApiController::class, 'destroySuspiciousFile'])->name('server.suspicious-files.destroy');
        Route::get('server/suspicious-files/download', [PasaxServerSecurityApiController::class, 'downloadSuspiciousFile'])->name('server.suspicious-files.download');
        Route::post('server/suspicious-files/verify', [PasaxServerSecurityApiController::class, 'verifySuspiciousFile'])->name('server.suspicious-files.verify');
        Route::post('server/suspicious-files/unverify', [PasaxServerSecurityApiController::class, 'unverifySuspiciousFile'])->name('server.suspicious-files.unverify');
        Route::get('server/location', [PasaxServerSecurityApiController::class, 'getLocation'])->name('server.location.get');
        Route::post('server/location', [PasaxServerSecurityApiController::class, 'updateLocation'])->name('server.location');
        Route::get('server/report/export', [PasaxServerSecurityApiController::class, 'exportReport'])->name('server.report.export');
        Route::post('server/optimize-clear', [PasaxServerSecurityApiController::class, 'optimizeClear'])->name('server.optimize-clear');
        Route::delete('lockouts/{id}', [PasaxServerSecurityApiController::class, 'releaseLockout'])->name('lockouts.destroy');

        // User Sessions & Trusted IPs
        Route::get('user-sessions', [PasaxUserSessionApiController::class, 'index'])->name('user-sessions.index');
        Route::get('user-sessions/realtime', [PasaxUserSessionApiController::class, 'realtime'])->name('user-sessions.realtime');
        Route::get('user-sessions/{id}', [PasaxUserSessionApiController::class, 'show'])->name('user-sessions.show');
        Route::delete('user-sessions/{id}', [PasaxUserSessionApiController::class, 'destroy'])->name('user-sessions.destroy');
        Route::delete('user-sessions/session/{sessionId}', [PasaxUserSessionApiController::class, 'destroySession'])->name('user-sessions.destroy-session');
        Route::delete('trusted-ips/{id}', [PasaxUserSessionApiController::class, 'destroyTrustedIp'])->name('trusted-ips.destroy');

        // Unblock Tickets Management
        Route::get('unblock-tickets', [PasaxIpUnblockRequestApiController::class, 'index'])->name('unblock-tickets.index');
        Route::post('unblock-tickets/{id}/respond', [PasaxIpUnblockRequestApiController::class, 'respond'])->name('unblock-tickets.respond');
        Route::delete('unblock-tickets/{id}', [PasaxIpUnblockRequestApiController::class, 'destroy'])->name('unblock-tickets.destroy');

        // Security Settings
        Route::get('settings', [PasaxSecuritySettingApiController::class, 'index'])->name('settings.index');
        Route::post('settings', [PasaxSecuritySettingApiController::class, 'update'])->name('settings.update');
    });
});
