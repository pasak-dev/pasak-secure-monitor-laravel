<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Internal\Pasak\Models\PasakLog;

function publishBladeViewsForLogTrend(): void
{
    $target = resource_path('views/pages/pasak');
    if (! File::isDirectory($target)) {
        File::copyDirectory(
            dirname(__DIR__, 2).'/stubs/blade/pages/pasak',
            $target
        );
    }
}

function registerLogTrendDummyRoutes(): void
{
    foreach ([
        'overview', 'logs', 'logs.show', 'logs.clear', 'logs.destroy',
        'blocked-ips', 'blocked-ips.show', 'blocked-ips.store', 'blocked-ips.toggle', 'blocked-ips.destroy',
        'server', 'sessions', 'tickets', 'tickets.show', 'tickets.respond', 'tickets.destroy', 'settings',
    ] as $name) {
        Route::get('/__pasak_test_'.$name, fn () => 'ok')->name('pasak.dashboard.'.$name);
    }

    Route::getRoutes()->refreshNameLookups();
}

test('logs dashboard page renders attack trend line chart with week, month, and year filters', function (): void {
    publishBladeViewsForLogTrend();
    registerLogTrendDummyRoutes();

    // Create a few logs across different dates
    PasakLog::query()->create([
        'ip_address' => '192.168.1.100',
        'event_type' => 'sqli_attempt',
        'threat_level' => 'critical',
        'path' => '/login',
        'rule_label' => 'SQL Injection',
        'was_blocked' => true,
        'created_at' => now(),
    ]);

    PasakLog::query()->create([
        'ip_address' => '10.0.0.50',
        'event_type' => 'xss_detected',
        'threat_level' => 'high',
        'path' => '/search',
        'rule_label' => 'Cross-Site Scripting',
        'was_blocked' => false,
        'created_at' => now()->subDays(2),
    ]);

    $controller = app(\Internal\Pasak\Http\Controllers\Dashboard\PasakLogController::class);

    // 1. Default (week)
    $requestWeek = request()->create('/logs', 'GET', ['trend_range' => 'week']);
    $viewWeek = $controller->index($requestWeek);
    $htmlWeek = $viewWeek->render();

    expect($viewWeek->getData())->toHaveKey('trend');
    expect($viewWeek->getData()['trendRange'])->toBe('week');
    expect($htmlWeek)->toContain('Attack trend');
    expect($htmlWeek)->toContain('polyline');
    expect($htmlWeek)->toContain('Minggu');
    expect($htmlWeek)->toContain('Bulan');
    expect($htmlWeek)->toContain('Tahun');

    // 2. Month range
    $requestMonth = request()->create('/logs', 'GET', ['trend_range' => 'month']);
    $viewMonth = $controller->index($requestMonth);
    expect($viewMonth->getData()['trendRange'])->toBe('month');
    $htmlMonth = $viewMonth->render();
    expect($htmlMonth)->toContain('Bulan (30 Hari)');

    // 3. Year range
    $requestYear = request()->create('/logs', 'GET', ['trend_range' => 'year']);
    $viewYear = $controller->index($requestYear);
    expect($viewYear->getData()['trendRange'])->toBe('year');
    $htmlYear = $viewYear->render();
    expect($htmlYear)->toContain('Tahun (12 Bulan)');
});
