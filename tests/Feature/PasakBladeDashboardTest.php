<?php

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Pasak\SecureMonitor\Http\Controllers\Dashboard\AllowedIpController;
use Pasak\SecureMonitor\Http\Controllers\Dashboard\OverviewController;
use Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakLogController;
use Pasak\SecureMonitor\Http\Controllers\Dashboard\PasakSessionController;
use Pasak\SecureMonitor\Http\Controllers\Dashboard\SettingController;
use Pasak\SecureMonitor\Models\AllowedIp;
use Pasak\SecureMonitor\Models\SecurityLog;
use Pasak\SecureMonitor\Models\TrustedIp;
use Pasak\SecureMonitor\Models\PasakUserLogin;
use Pasak\SecureMonitor\Services\PasakService;
use Pasak\SecureMonitor\Services\PasakUserLoginService;
use Pasak\SecureMonitor\Services\PasakVersionCheckService;
use Pasak\SecureMonitor\Support\PasakDashboardView;

test('dashboard overview renders plain blade views when driver is blade', function () {
    config()->set('pasak.dashboard.driver', 'blade');
    app()->detectEnvironment(fn () => 'local');
    config()->set('app.debug', true);

    // Terbitkan stub Blade murni ke view path bawaan testbench.
    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    Route::get('/__pasak_test_overview', fn () => 'ok')->name('pasak.dashboard.overview');

    // Layout navigasi membutuhkan seluruh nama rute dashboard.
    foreach (['logs', 'blocked-ips', 'server', 'sessions', 'tickets', 'settings'] as $page) {
        Route::get('/__pasak_test_'.$page, fn () => 'ok')->name('pasak.dashboard.'.$page);
    }

    Route::getRoutes()->refreshNameLookups();

    try {
        $response = app(OverviewController::class)(
            Request::create('/__pasak_test_overview?range=week'),
            app(PasakService::class)
        );

        expect($response)->toBeInstanceOf(View::class)
            ->and($response->getName())->toBe('pages.pasak.overview');

        $html = $response->render();

        expect($html)->toContain('Security Overview')
            ->and($html)->toContain('sec-stat-card')
            ->and($html)->toContain('<!DOCTYPE html>')
            ->and($html)->toContain('pasakAnnounce');
    } finally {
        File::deleteDirectory($target);
    }
});

test('dashboard allowed-ips page renders with entries', function () {
    config()->set('pasak.dashboard.driver', 'blade');

    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    foreach (['overview', 'logs', 'blocked-ips', 'server', 'sessions', 'tickets', 'settings'] as $page) {
        Route::get('/__pasak_test_'.$page, fn () => 'ok')->name('pasak.dashboard.'.$page);
    }
    Route::get('/__pasak_test_allowed', fn () => 'ok')->name('pasak.dashboard.allowed-ips');
    Route::post('/__pasak_test_allowed', fn () => 'ok')->name('pasak.dashboard.allowed-ips.store');
    Route::patch('/__pasak_test_allowed/{id}', fn () => 'ok')->name('pasak.dashboard.allowed-ips.toggle');
    Route::delete('/__pasak_test_allowed/{id}', fn () => 'ok')->name('pasak.dashboard.allowed-ips.destroy');

    Route::getRoutes()->refreshNameLookups();

    try {
        AllowedIp::create([
            'ip_address' => '10.9.0.0/16',
            'label' => 'Test range',
            'is_active' => true,
        ]);

        // Render langsung tanpa middleware web: sediakan $errors manual.
        $this->app['view']->share('errors', new ViewErrorBag);

        $response = app(AllowedIpController::class)->index(
            Request::create('/__pasak_test_allowed')
        );

        expect($response)->toBeInstanceOf(View::class);

        $html = $response->render();

        expect($html)->toContain('Allowed IPs')
            ->and($html)->toContain('10.9.0.0/16')
            ->and($html)->toContain('Test range');
    } finally {
        File::deleteDirectory($target);
    }
});

test('dashboard overview always renders blade views regardless of legacy driver value', function () {
    config()->set('pasak.dashboard.driver', 'react');

    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    Route::get('/__pasak_test_overview', fn () => 'ok')->name('pasak.dashboard.overview');

    // Layout navigasi membutuhkan seluruh nama rute dashboard.
    foreach (['logs', 'blocked-ips', 'server', 'sessions', 'tickets', 'settings'] as $page) {
        Route::get('/__pasak_test_'.$page, fn () => 'ok')->name('pasak.dashboard.'.$page);
    }

    Route::getRoutes()->refreshNameLookups();

    try {
        $response = app(OverviewController::class)(
            Request::create('/__pasak_test_overview?range=week'),
            app(PasakService::class)
        );

        // Dashboard kini Blade murni: nilai driver warisan diabaikan.
        expect($response)->toBeInstanceOf(View::class);
    } finally {
        File::deleteDirectory($target);
    }
});

test('dashboard settings page renders per-level thresholds and simulation summary', function () {
    config()->set('pasak.dashboard.driver', 'blade');

    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    foreach (['overview', 'logs', 'blocked-ips', 'server', 'sessions', 'tickets', 'settings'] as $page) {
        Route::get('/__pasak_test_'.$page, fn () => 'ok')->name('pasak.dashboard.'.$page);
    }
    Route::post('/__pasak_test_settings', fn () => 'ok')->name('pasak.dashboard.settings.update');

    Route::getRoutes()->refreshNameLookups();

    try {
        app(PasakService::class)->updateSettings([
            'auto_block_scope' => 'auto',
            'auto_block_level_thresholds' => ['critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 5],
            'auto_block_combined_threshold' => 3,
            'auto_block_enabled' => true,
        ]);

        $response = app(SettingController::class)->index(
            app(PasakVersionCheckService::class)
        );

        $html = $response->render();

        expect($html)->toContain('auto_block_level_thresholds')
            ->and($html)->toContain('value="auto"')
            ->and($html)->toContain('pasak-sim-chips')
            ->and($html)->toContain('data-sec-sim-level="critical"')
            ->and($html)->toContain('auto_block_duration_select')
            ->and($html)->toContain('login_lockout_enabled');
    } finally {
        File::deleteDirectory($target);
    }
});

test('dashboard store-my-ip action stores trusted ip with parsed OS and browser safely', function () {
    $user = $this->createAdminUser();
    $longUserAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36';

    $request = Request::create('/__pasak_test_store_my_ip', 'POST', [], [], [], [
        'REMOTE_ADDR' => '158.140.185.154',
        'HTTP_USER_AGENT' => $longUserAgent,
    ]);
    $request->setUserResolver(fn () => $user);

    $response = app(PasakSessionController::class)->storeMyIp(
        $request,
        app(PasakService::class),
        app(PasakUserLoginService::class),
    );

    expect($response->isRedirection())->toBeTrue();

    $trusted = TrustedIp::query()
        ->where('user_id', $user->id)
        ->where('ip_address', '158.140.185.154')
        ->first();

    expect($trusted)->not->toBeNull()
        ->and($trusted->operating_system)->toBe('Linux')
        ->and($trusted->browser)->toContain('Google Chrome');
});
test('dashboard view attackerInfo classifies scanners bots and crawlers accurately', function () {
    $DV = PasakDashboardView::class;

    // 1. Scanner
    $sqlmap = $DV::attackerInfo('sqlmap/1.7.8#stable (https://sqlmap.org)', 'sqli_attempt');
    expect($sqlmap['category'])->toBe('scanner')
        ->and($sqlmap['is_bot'])->toBeTrue()
        ->and($sqlmap['icon'])->toBe('🚨')
        ->and($sqlmap['name'])->toContain('SQLMap');

    // 2. Automated Script
    $curl = $DV::attackerInfo('curl/7.88.1');
    expect($curl['category'])->toBe('bot')
        ->and($curl['is_bot'])->toBeTrue()
        ->and($curl['icon'])->toBe('🤖')
        ->and($curl['name'])->toContain('cURL');

    // 3. Web Crawler
    $google = $DV::attackerInfo('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');
    expect($google['category'])->toBe('crawler')
        ->and($google['is_bot'])->toBeTrue()
        ->and($google['icon'])->toBe('🕷️')
        ->and($google['name'])->toBe('Googlebot');

    // 4. Headless / Empty UA
    $empty = $DV::attackerInfo('');
    expect($empty['category'])->toBe('headless')
        ->and($empty['is_bot'])->toBeTrue()
        ->and($empty['icon'])->toBe('🕵️');

    // 5. Standard Browser
    $chrome = $DV::attackerInfo('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    expect($chrome['category'])->toBe('browser')
        ->and($chrome['is_bot'])->toBeFalse()
        ->and($chrome['name'])->toContain('Google Chrome');
});

test('pasak log controller supports bot_filter query parameter', function () {
    config()->set('pasak.dashboard.driver', 'blade');
    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    Route::get('/__pasak_test_logs', fn () => 'ok')->name('pasak.dashboard.logs');
    Route::get('/__pasak_test_logs/{id}', fn () => 'ok')->name('pasak.dashboard.logs.show');
    Route::delete('/__pasak_test_logs_clear', fn () => 'ok')->name('pasak.dashboard.logs.clear');
    Route::delete('/__pasak_test_logs/{id}', fn () => 'ok')->name('pasak.dashboard.logs.destroy');
    Route::getRoutes()->refreshNameLookups();

    try {
        SecurityLog::create([
            'ip_address' => '1.1.1.1',
            'event_type' => 'scan',
            'threat_level' => 'critical',
            'path' => '/admin',
            'user_agent' => 'sqlmap/1.7#stable',
        ]);
        SecurityLog::create([
            'ip_address' => '2.2.2.2',
            'event_type' => 'crawler',
            'threat_level' => 'low',
            'path' => '/robots.txt',
            'user_agent' => 'Googlebot/2.1',
        ]);
        SecurityLog::create([
            'ip_address' => '3.3.3.3',
            'event_type' => 'normal',
            'threat_level' => 'low',
            'path' => '/home',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);

        // Filter scanner
        $reqScanner = Request::create('/__pasak_test_logs?bot_filter=scanner');
        $resScanner = app(PasakLogController::class)->index($reqScanner);
        $logsScanner = $resScanner->getData()['logs'];
        expect($logsScanner->total())->toBe(1)
            ->and($logsScanner->first()->ip_address)->toBe('1.1.1.1');

        // Filter crawler
        $reqCrawler = Request::create('/__pasak_test_logs?bot_filter=crawler');
        $resCrawler = app(PasakLogController::class)->index($reqCrawler);
        $logsCrawler = $resCrawler->getData()['logs'];
        expect($logsCrawler->total())->toBe(1)
            ->and($logsCrawler->first()->ip_address)->toBe('2.2.2.2');
    } finally {
        File::deleteDirectory($target);
    }
});

test('dashboard sessions page shows modal to trust IP when current IP is not yet trusted', function () {
    config()->set('pasak.dashboard.driver', 'blade');

    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    $user = $this->createAdminUser();

    Route::get('/__pasak_test_sessions_modal', fn () => 'ok')->name('pasak.dashboard.sessions');
    Route::delete('/__pasak_test_user_sessions/{id}', fn () => 'ok')->name('pasak.dashboard.user-sessions.destroy');
    Route::delete('/__pasak_test_sessions/{sessionId}', fn () => 'ok')->name('pasak.dashboard.sessions.destroy');
    Route::delete('/__pasak_test_trusted_ips/{id}', fn () => 'ok')->name('pasak.dashboard.trusted-ips.destroy');
    Route::post('/__pasak_test_trusted_ips/my-ip', fn () => 'ok')->name('pasak.dashboard.trusted-ips.store-my-ip');

    foreach (['overview', 'logs', 'blocked-ips', 'server', 'tickets', 'settings', 'allowed-ips', 'threat-map'] as $page) {
        Route::get('/__pasak_test_'.$page, fn () => 'ok')->name('pasak.dashboard.'.$page);
    }
    Route::getRoutes()->refreshNameLookups();

    try {
        $request = Request::create('/__pasak_test_sessions_modal', 'GET', [], [], [], [
            'REMOTE_ADDR' => '103.20.10.55',
        ]);
        $request->setUserResolver(fn () => $user);

        $response = app(PasakSessionController::class)->index($request);

        expect($response)->toBeInstanceOf(View::class)
            ->and($response->getData()['isCurrentIpTrusted'])->toBeFalse();

        $html = $response->render();

        expect($html)->toContain('id="pasakTrustIpModal"')
            ->and($html)->toContain('103.20.10.55')
            ->and($html)->toContain('pasakOpenTrustIpModal')
            ->and($html)->toContain(route('pasak.dashboard.trusted-ips.store-my-ip'));
    } finally {
        File::deleteDirectory($target);
    }
});

test('dashboard sessions page skips modal when current IP is already trusted', function () {
    config()->set('pasak.dashboard.driver', 'blade');

    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    $user = $this->createAdminUser();

    // Simpan IP 103.20.10.55 sebagai IP terpercaya
    TrustedIp::query()->create([
        'user_id' => $user->id,
        'ip_address' => '103.20.10.55',
        'is_active' => true,
        'verified_at' => now(),
    ]);

    Route::get('/__pasak_test_sessions_skip', fn () => 'ok')->name('pasak.dashboard.sessions');
    Route::delete('/__pasak_test_user_sessions/{id}', fn () => 'ok')->name('pasak.dashboard.user-sessions.destroy');
    Route::delete('/__pasak_test_sessions/{sessionId}', fn () => 'ok')->name('pasak.dashboard.sessions.destroy');
    Route::delete('/__pasak_test_trusted_ips/{id}', fn () => 'ok')->name('pasak.dashboard.trusted-ips.destroy');
    Route::post('/__pasak_test_trusted_ips/my-ip', fn () => 'ok')->name('pasak.dashboard.trusted-ips.store-my-ip');

    foreach (['overview', 'logs', 'blocked-ips', 'server', 'tickets', 'settings', 'allowed-ips', 'threat-map'] as $page) {
        Route::get('/__pasak_test_'.$page, fn () => 'ok')->name('pasak.dashboard.'.$page);
    }
    Route::getRoutes()->refreshNameLookups();

    try {
        $request = Request::create('/__pasak_test_sessions_skip', 'GET', [], [], [], [
            'REMOTE_ADDR' => '103.20.10.55',
        ]);
        $request->setUserResolver(fn () => $user);

        $response = app(PasakSessionController::class)->index($request);

        expect($response)->toBeInstanceOf(View::class)
            ->and($response->getData()['isCurrentIpTrusted'])->toBeTrue();

        $html = $response->render();

        // Modal dilewati / tidak dirender
        expect($html)->not->toContain('id="pasakTrustIpModal"')
            ->and($html)->toContain(route('pasak.dashboard.trusted-ips.destroy', 1));
    } finally {
        File::deleteDirectory($target);
    }
});

test('dashboard store-my-ip resolves user from pasak dashboard session when laravel user is null', function () {
    $user = $this->createAdminUser();

    $request = Request::create('/__pasak_test_session_store_my_ip', 'POST', [], [], [], [
        'REMOTE_ADDR' => '103.20.10.99',
    ]);
    // Simulasi sesi auth mandiri Pasak tanpa user guard Laravel
    $session = app('session.store');
    $session->put('pasak_dashboard_authenticated', true);
    $session->put('pasak_dashboard_user', $user->email);
    $request->setLaravelSession($session);

    $response = app(PasakSessionController::class)->storeMyIp(
        $request,
        app(PasakService::class),
        app(PasakUserLoginService::class),
    );

    expect($response->isRedirection())->toBeTrue();

    $trusted = TrustedIp::query()
        ->where('ip_address', '103.20.10.99')
        ->first();

    expect($trusted)->not->toBeNull()
        ->and((int) $trusted->user_id)->toBe((int) $user->id);
});

test('dashboard sessions page only displays users who accessed the pasak dashboard', function () {
    config()->set('pasak.dashboard.driver', 'blade');

    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }

    $regularUser = $this->createAdminUser(['email' => 'regular@example.com', 'name' => 'Regular User']);
    $dashboardUser = $this->createAdminUser(['email' => 'admin-pasak@example.com', 'name' => 'Pasak Admin']);

    // 1. Sesi login reguler (bukan dashboard)
    PasakUserLogin::query()->create([
        'user_id' => $regularUser->id,
        'session_id' => 'regular-web-session',
        'ip_address' => '10.10.10.1',
        'user_agent' => 'Mozilla/5.0 Regular User',
        'device_type' => 'desktop',
        'operating_system' => 'Linux',
        'browser' => 'Chrome',
        'login_at' => now(),
        'last_activity_at' => now(),
    ]);

    // 2. Sesi login dashboard Pasak
    PasakUserLogin::query()->create([
        'user_id' => $dashboardUser->id,
        'session_id' => 'pasak-dashboard-session',
        'ip_address' => '10.10.10.2',
        'user_agent' => 'Mozilla/5.0 Pasak Admin',
        'device_type' => 'pasak_dashboard',
        'operating_system' => 'macOS',
        'browser' => 'Firefox',
        'login_at' => now(),
        'last_activity_at' => now(),
    ]);

    Route::get('/__pasak_test_sessions_filter', fn () => 'ok')->name('pasak.dashboard.sessions');
    Route::delete('/__pasak_test_user_sessions/{id}', fn () => 'ok')->name('pasak.dashboard.user-sessions.destroy');
    Route::delete('/__pasak_test_sessions/{sessionId}', fn () => 'ok')->name('pasak.dashboard.sessions.destroy');
    Route::delete('/__pasak_test_trusted_ips/{id}', fn () => 'ok')->name('pasak.dashboard.trusted-ips.destroy');
    Route::post('/__pasak_test_trusted_ips/my-ip', fn () => 'ok')->name('pasak.dashboard.trusted-ips.store-my-ip');

    foreach (['overview', 'logs', 'blocked-ips', 'server', 'tickets', 'settings', 'allowed-ips', 'threat-map'] as $page) {
        Route::get('/__pasak_test_'.$page, fn () => 'ok')->name('pasak.dashboard.'.$page);
    }
    Route::getRoutes()->refreshNameLookups();

    try {
        $request = Request::create('/__pasak_test_sessions_filter', 'GET');
        $session = app('session.store');
        $session->setId('pasak-dashboard-session');
        $session->put('pasak_dashboard_authenticated', true);
        $request->setLaravelSession($session);
        $request->setUserResolver(fn () => $dashboardUser);

        $response = app(PasakSessionController::class)->index($request);

        expect($response)->toBeInstanceOf(View::class);

        $logins = $response->getData()['logins'];
        $loginIps = $logins->pluck('ip_address')->all();

        // Hanya sesi dashboard yang tampil
        expect($loginIps)->toContain('10.10.10.2')
            ->and($loginIps)->not->toContain('10.10.10.1');

        $html = $response->render();
        expect($html)->toContain('10.10.10.2')
            ->and($html)->toContain('Pasak Admin')
            ->and($html)->not->toContain('10.10.10.1')
            ->and($html)->not->toContain('Regular User');
    } finally {
        File::deleteDirectory($target);
    }
});
