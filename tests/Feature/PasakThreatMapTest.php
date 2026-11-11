<?php

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Internal\Pasak\Http\Controllers\Dashboard\PasakThreatMapController;
use Internal\Pasak\Models\PasakLog;
use Internal\Pasak\Services\PasakService;

beforeEach(function () {
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
    Route::get('/__pasak_test_threat', fn () => 'ok')->name('pasak.dashboard.threat-map');
    Route::get('/__pasak_test_threat/feed', fn () => 'ok')->name('pasak.dashboard.threat-map.feed');

    Route::getRoutes()->refreshNameLookups();

    $this->target = resource_path('views/pages/pasak');
});

afterEach(function () {
    File::deleteDirectory($this->target);
});

test('threat map feed returns recent attack logs with resolved locations', function () {
    Http::fake();

    PasakLog::create([
        'ip_address' => '127.0.0.1',
        'event_type' => 'threat_detected',
        'threat_level' => 'critical',
        'method' => 'POST',
        'path' => '/wls-wsat/CoordinatorPortType',
        'rule_label' => 'Oracle WebLogic RCE',
        'was_blocked' => true,
    ]);

    $this->app['view']->share('errors', new ViewErrorBag);

    $controller = app(PasakThreatMapController::class);

    $response = $controller->feed(
        Request::create('/__pasak_test_threat/feed?after=0'),
        app(PasakService::class)
    );

    expect($response)->toBeInstanceOf(JsonResponse::class);

    $data = $response->getData(true);

    expect($data['logs'])->toHaveCount(1)
        ->and($data['logs'][0]['ip'])->toBe('127.0.0.1')
        ->and($data['logs'][0]['level'])->toBe('critical')
        ->and($data['logs'][0]['desc'])->toBe('Oracle WebLogic RCE')
        ->and($data['locations']['127.0.0.1']['lat'])->toBe(0.5071)
        ->and($data['locations']['127.0.0.1']['lon'])->toBe(101.4478)
        ->and($data['locations']['127.0.0.1']['place'])->toBe('Pekanbaru')
        ->and($data['stats'])->toHaveKeys(['logs_today', 'blocked_attempts_today']);
});

test('threat map feed only returns entries newer than the given identifier', function () {
    Http::fake();

    $older = PasakLog::create([
        'ip_address' => '127.0.0.1',
        'event_type' => 'threat_detected',
        'threat_level' => 'low',
    ]);

    PasakLog::create([
        'ip_address' => '127.0.0.1',
        'event_type' => 'threat_detected',
        'threat_level' => 'high',
    ]);

    $controller = app(PasakThreatMapController::class);

    $response = $controller->feed(
        Request::create('/__pasak_test_threat/feed?after='.$older->id),
        app(PasakService::class)
    );

    $data = $response->getData(true);

    expect($data['logs'])->toHaveCount(1)
        ->and($data['logs'][0]['level'])->toBe('high')
        ->and($data['last_id'])->toBe($older->id + 1);
});

test('threat map page renders globe canvas with real attacker origins', function () {
    Http::fake();

    PasakLog::create([
        'ip_address' => '127.0.0.1',
        'event_type' => 'threat_detected',
        'threat_level' => 'critical',
        'method' => 'GET',
        'path' => '/storage/.env',
        'rule_label' => 'Sensitive File Access',
        'was_blocked' => true,
    ]);
    PasakLog::create([
        'ip_address' => '127.0.0.1',
        'event_type' => 'threat_detected',
        'threat_level' => 'low',
        'path' => '/health',
    ]);

    $this->app['view']->share('errors', new ViewErrorBag);

    $response = app(PasakThreatMapController::class)->index(
        Request::create('/__pasak_test_threat'),
        app(PasakService::class)
    );

    expect($response)->toBeInstanceOf(View::class)
        ->and($response->getName())->toBe('pages.pasak.threat-map');

    $html = $response->render();

    expect($html)->toContain('threat-map-card')
        ->and($html)->toContain('[data-map-base]')
        ->and($html)->toContain('127.0.0.1')
        ->and($html)->toContain('Sensitive File Access')
        ->and($html)->toContain('feedUrl')
        ->and($html)->toContain('Pekanbaru, Riau (ID)');
});

test('threat map payload honors configured window and target overrides', function () {
    Http::fake();

    config()->set('pasak.dashboard.threat_map.target_name', 'Jakarta, DKI (ID)');
    config()->set('pasak.dashboard.threat_map.window_hours', 6);

    PasakLog::create([
        'ip_address' => '127.0.0.1',
        'event_type' => 'threat_detected',
        'threat_level' => 'medium',
    ]);

    $this->app['view']->share('errors', new ViewErrorBag);

    $response = app(PasakThreatMapController::class)->index(
        Request::create('/__pasak_test_threat'),
        app(PasakService::class)
    );

    $payload = $response->getData()['payload'];

    expect($payload['target']['name'])->toBe('Jakarta, DKI (ID)')
        ->and($payload['window'])->toBe(6)
        ->and($payload['origins'])->toHaveCount(1)
        ->and($payload['origins'][0]['ip'])->toBe('127.0.0.1')
        ->and($payload['origins'][0]['hits'])->toBe(1)
        ->and($payload['origins'][0]['weight'])->toBe(2);
});

test('threat map payload passes recent security logs and origins to threat map', function () {
    Http::fake();

    PasakLog::create([
        'ip_address' => '203.0.113.50',
        'event_type' => 'sqli_detected',
        'threat_level' => 'critical',
        'method' => 'POST',
        'path' => '/login',
        'rule_label' => 'SQL Injection in Auth',
        'was_blocked' => true,
    ]);

    PasakLog::create([
        'ip_address' => '203.0.113.60',
        'event_type' => 'xss_detected',
        'threat_level' => 'high',
        'method' => 'GET',
        'path' => '/search',
        'rule_label' => 'Cross-Site Scripting Attempt',
        'was_blocked' => false,
    ]);

    $this->app['view']->share('errors', new ViewErrorBag);

    $response = app(PasakThreatMapController::class)->index(
        Request::create('/__pasak_test_threat'),
        app(PasakService::class)
    );

    $payload = $response->getData()['payload'];

    expect($payload['recent_logs'])->toHaveCount(2)
        ->and($payload['recent_logs'][0]['ip'])->toBe('203.0.113.60')
        ->and($payload['recent_logs'][0]['desc'])->toBe('Cross-Site Scripting Attempt')
        ->and($payload['recent_logs'][1]['ip'])->toBe('203.0.113.50')
        ->and($payload['recent_logs'][1]['desc'])->toBe('SQL Injection in Auth')
        ->and($payload['origins'])->toHaveCount(2);
});

test('threat map auto detects server location when manual config is not provided', function () {
    Cache::forget('pasak:threat_map_server_target');

    config()->set('pasak.dashboard.threat_map.target_name', null);
    config()->set('pasak.dashboard.threat_map.target_lat', null);
    config()->set('pasak.dashboard.threat_map.target_lon', null);
    config()->set('pasak.dashboard.threat_map.auto_detect_target', true);

    Http::fake([
        'http://ip-api.com/*' => Http::response([
            'status' => 'success',
            'city' => 'Jakarta',
            'regionName' => 'DKI Jakarta',
            'country' => 'Indonesia',
            'countryCode' => 'ID',
            'lat' => -6.2088,
            'lon' => 106.8456,
        ], 200),
    ]);

    $this->app['view']->share('errors', new ViewErrorBag);

    $response = app(PasakThreatMapController::class)->index(
        Request::create('/__pasak_test_threat'),
        app(PasakService::class)
    );

    $payload = $response->getData()['payload'];

    expect($payload['target']['name'])->toBe('Jakarta, DKI Jakarta (ID)')
        ->and($payload['target']['lat'])->toBe(-6.2088)
        ->and($payload['target']['lon'])->toBe(106.8456);
});

test('upgrade warns when published dashboard layout lacks the threat map menu', function () {
    Http::fake([
        'https://repo.packagist.org/p2/pasak-dev/pasak-secure-monitor-laravel.json' => Http::response([
            'packages' => ['pasak-dev/pasak-secure-monitor-laravel' => [['version' => 'v99.9.9']]],
        ], 200),
        'https://api.github.com/repos/pasak-dev/pasak-secure-monitor-laravel/tags' => Http::response([], 200),
    ]);

    $layout = resource_path('views/pages/pasak/layout.blade.php');
    File::ensureDirectoryExists(dirname($layout));
    $original = File::exists($layout) ? File::get($layout) : null;
    File::put($layout, "@extends('x') pasakAnnounce {{ route('pasak.dashboard.overview') }}");

    try {
        $this->artisan('pasak:upgrade', [
            '--no-composer' => true,
            '--no-migrate' => true,
        ])->expectsOutputToContain('kedaluwarsa')
            ->expectsOutputToContain('--sync-views')
            ->assertSuccessful();

        // Tanpa --sync-views layout kustom host tidak ditimpa:
        expect(File::get($layout))->toBe("@extends('x') pasakAnnounce {{ route('pasak.dashboard.overview') }}");
    } finally {
        if ($original !== null) {
            File::put($layout, $original);
        }
    }
});
