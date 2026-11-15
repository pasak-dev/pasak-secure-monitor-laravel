<?php

use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Http\Middleware\InjectAnnouncementBanner;

beforeEach(function () {
    app()->detectEnvironment(fn () => 'local');
    config()->set('app.debug', true);
    config()->set('pasax.announcement.enabled', true);
    config()->set('pasax.announcement.paths', ['__pasax_home']);

    Route::middleware(InjectAnnouncementBanner::class)->group(function () {
        Route::get('/__pasax_home', fn () => response(
            '<html><head><title>Home</title></head><body><h1>Welcome</h1></body></html>'
        ));
        Route::get('/__pasax_inner', fn () => response(
            '<html><body><h1>Inner</h1></body></html>'
        ));
        Route::get('/__pasax_json', fn () => response()->json(['ok' => true]));
    });
});

test('announcement banner and dialog are injected on configured homepage', function () {
    $response = $this->get('/__pasax_home');

    $response->assertOk();
    $html = $response->getContent();

    expect($html)->toContain('id="psk-announce-bar"')
        ->and($html)->toContain('Dilindungi')
        ->and($html)->toContain('id="psk-announce-modal"')
        ->and($html)->toContain('pasax-announce-')
        ->and($html)->toContain('<h1>Welcome</h1>');
});

test('announcement is skipped outside configured paths', function () {
    $html = $this->get('/__pasax_inner')->getContent();

    expect($html)->not->toContain('psk-announce-bar');
});

test('announcement is skipped for json responses', function () {
    $html = $this->getJson('/__pasax_json')->getContent();

    expect($html)->not->toContain('psk-announce-bar');
});

test('announcement can be disabled via config', function () {
    config()->set('pasax.announcement.enabled', false);

    $html = $this->get('/__pasax_home')->getContent();

    expect($html)->not->toContain('psk-announce-bar');
});

test('announcement is skipped in production even when enabled', function () {
    app()->detectEnvironment(fn () => 'production');
    config()->set('app.debug', false);

    try {
        $html = $this->get('/__pasax_home')->getContent();
    } catch (Throwable $e) {
        app()->detectEnvironment(fn () => 'testing');
        throw $e;
    }

    app()->detectEnvironment(fn () => 'testing');

    expect($html)->not->toContain('psk-announce-bar');
});

test('announcement middleware alias is registered', function () {
    expect(app('router')->getMiddleware())->toHaveKey('pasax.announce');
});

test('env stub stays core-only and announcement is a database-backed setting', function () {
    $stub = file_get_contents(dirname(__DIR__, 2).'/stubs/env.stub');

    // Env inti hanya memuat sakelar boot/routing; perilaku pengumuman
    // dikelola dari dashboard (pasax_settings) dan dibaca lewat bawaan
    // konfigurasi bila belum diatur.
    expect($stub)->toContain('PASAX_MONITOR_ENABLED')
        ->and($stub)->not->toContain('PASAX_ANNOUNCEMENT_ENABLED');
});
