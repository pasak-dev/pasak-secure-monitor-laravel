<?php

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Pasax\SecureMonitor\Http\Controllers\Dashboard\OverviewController;
use Pasax\SecureMonitor\Services\PasaxService;

test('dashboard defaults to english locale and renders support button with language switcher', function () {
    config()->set('pasax.dashboard.driver', 'blade');
    app()->detectEnvironment(fn () => 'local');
    config()->set('app.debug', true);

    $target = resource_path('views/pages/pasax');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasax',
            $target
        );
    }

    Route::get('/__test_overview', fn () => 'ok')->name('pasax.dashboard.overview');
    foreach (['logs', 'threat-map', 'blocked-ips', 'allowed-ips', 'server', 'sessions', 'tickets', 'settings'] as $page) {
        Route::get('/__test_'.$page, fn () => 'ok')->name('pasax.dashboard.'.$page);
    }
    Route::getRoutes()->refreshNameLookups();

    try {
        // 1. Default request without lang query should default to 'en'
        $response = app(OverviewController::class)(
            $r1 = Request::create('/__test_overview?range=week'),
            app(PasaxService::class)
        );
        app()->instance('request', $r1);

        expect($response)->toBeInstanceOf(View::class);
        $html = $response->render();

        expect(app()->getLocale())->toBe('en')
            ->and($html)->toContain('data-sec-header-support')
            ->and($html)->toContain('sec-lang-switcher')
            ->and($html)->toContain('pasaxPageSupportModal')
            ->and($html)->toContain('pasaxSetLocale')
            ->and($html)->toContain('Security Overview');

        // 2. Request with lang=id should switch to 'id'
        $responseId = app(OverviewController::class)(
            $r1 = Request::create('/__test_overview?range=week&lang=id'),
            app(PasaxService::class)
        );
        app()->instance('request', $r1);

        $htmlId = $responseId->render();
        expect(app()->getLocale())->toBe('id')
            ->and($htmlId)->toContain('Ringkasan Keamanan');
    } finally {
        File::deleteDirectory($target);
    }
});
